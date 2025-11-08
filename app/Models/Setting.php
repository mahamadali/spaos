<?php

namespace App\Models;

use App\Models\Traits\HasHashedMediaTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;

class Setting extends BaseModel implements HasMedia
{
    use HasHashedMediaTrait;
    use SoftDeletes;

    protected $table = 'settings';

    /**
     * Add a settings value.
     *
     * @param  string  $type
     * @return bool
     */
    public static function add($key, $val, $type = 'string')
    {
        if (self::has($key)) {
            return self::set($key, $val, $type);
        }

        return self::create(['name' => $key, 'val' => $val, 'type' => $type]);
    }

    /**
     * Get a settings value.
     *
     * @param  null  $default
     * @return bool|int|mixed
     */
    public static function get($key, $default = null)
    {
        if (self::has($key)) {
            $setting = self::getAllSettings()->where('name', $key)->first();

            return self::castValue($setting->val, $setting->type);
        }

        return self::getDefaultValue($key, $default);
    }

    /**
     * Get a settings value with vendor fallback to Super Admin.
     *
     * @param  string  $key
     * @param  null  $default
     * @param  int|null  $userId
     * @return mixed
     */
    public static function getWithFallback($key, $default = null, $userId = null)
    {
        if ($userId === null) {
            $userId = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null;
        }

        // First try to get vendor's custom setting
        $vendorSetting = self::where('name', $key)
            ->where('created_by', $userId)
            ->first();

        if ($vendorSetting) {
            $value = self::castValue($vendorSetting->val, $vendorSetting->type);
            
            // Format phone number if it's helpline_number
            if ($key === 'helpline_number' && !empty($value)) {
                $value = self::formatPhoneNumber($value);
            }
            
            return $value;
        }

        // Fallback to Super Admin settings (user_id = 1)
        $superAdminSetting = self::where('name', $key)
            ->where('created_by', 1)
            ->first();

        if ($superAdminSetting) {
            $value = self::castValue($superAdminSetting->val, $superAdminSetting->type);
            
            // Format phone number if it's helpline_number
            if ($key === 'helpline_number' && !empty($value)) {
                $value = self::formatPhoneNumber($value);
            }
            
            return $value;
        }

        // Final fallback to default value
        $defaultValue = self::getDefaultValue($key, $default);
        
        // Format phone number if it's helpline_number and we're using default
        if ($key === 'helpline_number' && !empty($defaultValue)) {
            $defaultValue = self::formatPhoneNumber($defaultValue);
        }
        
        return $defaultValue;
    }

    /**
     * Set a value for setting.
     *
     * @param  string  $type
     * @return bool
     */
    public static function set($key, $val, $type = 'string')
    {
        if ($setting = self::getAllSettings()->where('name', $key)->first()) {
            return $setting->update([
                'name' => $key,
                'val' => $val,
                'type' => $type,
            ]) ? $setting : false;
        }

        return self::add($key, $val, $type);
    }

    /**
     * Remove a setting.
     *
     * @return bool
     */
    public static function remove($key)
    {
        if (self::has($key)) {
            return self::whereName($key)->delete();
        }

        return false;
    }

    /**
     * Check if setting exists.
     *
     * @return bool
     */
    public static function has($key)
    {
        return (bool) self::getAllSettings()->whereStrict('name', $key)->count();
    }

    /**
     * Get the validation rules for setting fields.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        return self::getDefinedSettingFields()->pluck('rules', 'name')
            ->reject(function ($val) {
                return is_null($val);
            })->toArray();
    }

    public static function getSelectedValidationRules($value)
    {
        return self::getDefinedSettingFields()->whereIn('name', $value)->pluck('rules', 'name')
            ->reject(function ($val) {
                return is_null($val);
            })->toArray();
    }

    /**
     * Get the data type of a setting.
     *
     * @return mixed
     */
    public static function getDataType($field)
    {
        $type = self::getDefinedSettingFields()
            ->pluck('data', 'name')
            ->get($field);

        return is_null($type) ? 'string' : $type;
    }

    /**
     * Get default value for a setting.
     *
     * @return mixed
     */
    public static function getDefaultValueForField($field)
    {
        return self::getDefinedSettingFields()
            ->pluck('value', 'name')
            ->get($field);
    }

    /**
     * Get default value from config if no value passed.
     *
     * @return mixed
     */
    private static function getDefaultValue($key, $default)
    {
        return is_null($default) ? self::getDefaultValueForField($key) : $default;
    }

    /**
     * Get all the settings fields from config.
     *
     * @return Collection
     */
    private static function getDefinedSettingFields()
    {
        return collect(config('setting_fields'))->pluck('elements')->flatten(1);
    }

    /**
     * caste value into respective type.
     *
     * @return bool|int
     */
    private static function castValue($val, $castTo)
    {
        switch ($castTo) {
            case 'int':
            case 'integer':
                return intval($val);
                break;

            case 'bool':
            case 'boolean':
                return boolval($val);
                break;

            default:
                return $val;
        }
    }

    /**
     * Get all the settings.
     *
     * @return mixed
     */
    public static function getAllSettings()
    {
        return Cache::rememberForever('settings.all', function () {
            return self::select('id', 'name', 'val')->get();
        });
    }

    /**
     * Flush the cache.
     */
    public static function flushCache()
    {
        Cache::forget('settings.all');
    }

    /**
     * Copy super admin settings to a new user.
     *
     * @param  int  $userId
     * @return void
     */
    public static function copySuperAdminSettingsToUser($userId)
    {
        $superAdminId = 1; // Super admin user ID
        
        // Get all super admin settings
        $superAdminSettings = self::where('created_by', $superAdminId)->get();
        
        foreach ($superAdminSettings as $setting) {
            // Check if user already has this setting
            $existingSetting = self::where('name', $setting->name)
                ->where('created_by', $userId)
                ->first();
            
            if (!$existingSetting) {
                // Create a copy of the setting for the new user
                self::create([
                    'name' => $setting->name,
                    'val' => $setting->val,
                    'type' => $setting->type,
                    'created_by' => $userId,
                ]);
            }
        }
        
        // Clear cache to ensure new settings are available
        self::flushCache();
    }

    /**
     * Format phone number to ensure proper country code format.
     *
     * @param  string  $phoneNumber
     * @return string
     */
    private static function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return '';
        }

        // If the number already starts with +, return as is
        if (str_starts_with($phoneNumber, '+')) {
            return $phoneNumber;
        }

        // Return the phone number as is without adding any country code
        return $phoneNumber;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function () {
            self::flushCache();
        });

        static::deleted(function () {
            self::flushCache();
        });
    }
}
