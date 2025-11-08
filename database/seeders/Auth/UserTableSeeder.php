<?php

namespace Database\Seeders\Auth;

use App\Events\Backend\UserCreated;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Class UserTableSeeder.
 */


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        // Add the master administrator, user id of 1
        $avatarPath = config('app.avatar_base_path');
        $super_Admin = User::updateOrCreate(
            ['email' => 'superadmin@salon.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'Super Admin',
                'slug' => 'super-admin',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 4578952512',
                'date_of_birth' => fake()->date,
                'avatar' => $avatarPath . 'male.webp',
                'user_type' => 'super admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        $super_Admin->assignRole('super admin');
        $admin = User::updateOrCreate(
            ['email' => 'admin@salon.com'],
            [
                'first_name' => 'Salon',
                'last_name' => 'Admin',
                'username' => 'Admin',
                'slug' => 'salon-admin',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 7485961545',
                'date_of_birth' => fake()->date,
                'user_type' => 'admin',
                'avatar' => $avatarPath . 'male.webp',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        $admin->assignRole('admin');
        $users = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'slug' => 'john-doe',
                'email' => 'john@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 2563987448',
                'date_of_birth' => fake()->date,
                'user_type' => 'admin',
                'profile_image' => public_path('/dummy-images/customers/13.webp'),
                'avatar' => $avatarPath . 'male.webp',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Richards',
                'slug' => 'john-richards',
                'email' => 'john.richards@hotmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 3565478912',
                'date_of_birth' => fake()->date,
                'user_type' => 'user',
                'avatar' => $avatarPath . 'male.webp',
                'profile_image' => public_path('/dummy-images/customers/8.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Alice',
                'last_name' => 'Thompson',
                'email' => 'alice.thompson@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 5674587110',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'user_type' => 'user',
                'profile_image' => public_path('/dummy-images/customers/11.webp'),
                'gender' => 'other',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Noah',
                'last_name' => 'Thompson',
                'email' => 'noah.thompson@yahoo.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 6589741258',
                'user_type' => 'user',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/2.webp'),
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Benjamin',
                'last_name' => 'Robinson',
                'email' => 'benjamin.robinson@yahoo.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 5687412589',
                'date_of_birth' => fake()->date,
                'user_type' => 'user',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/4.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Liam',
                'last_name' => 'Wilson',
                'email' => 'liam.wilson@yahoo.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 6352897456',
                'user_type' => 'user',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/3.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ethan',
                'last_name' => 'Brown',
                'email' => 'ethan.brown@hotmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 3652417895',
                'date_of_birth' => fake()->date,
                'user_type' => 'user',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/5.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'William',
                'last_name' => 'Turner',
                'email' => 'william.turner@hotmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 8874547968',
                'user_type' => 'user',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/6.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Isabella',
                'last_name' => 'Martin',
                'email' => 'isabella.martin@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 3652547855',
                'date_of_birth' => fake()->date,
                'user_type' => 'user',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/10.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Johnson',
                'email' => 'emma.johnson@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 4785478627',
                'user_type' => 'user',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/11.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Olivia',
                'last_name' => 'Davis',
                'email' => 'olivia.davis@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 9685873657',
                'date_of_birth' => fake()->date,
                'user_type' => 'user',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_by' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 5645476635',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Alex',
                'last_name' => 'Johnson',
                'email' => 'alex@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 4283488721',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 6755443584',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Chris',
                'last_name' => 'Brown',
                'email' => 'chris@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 5838159285',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 3935968664',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Jessica',
                'last_name' => 'Jones',
                'email' => 'jessica@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 7386438485',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Jacob',
                'last_name' => 'Miller',
                'email' => 'jacob@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 6469268853',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ashley',
                'last_name' => 'Davis',
                'email' => 'ashley@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 2783445627',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Christopher',
                'last_name' => 'Garcia',
                'email' => 'christopher@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 7283449667',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Rodriguez',
                'email' => 'sarah@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+1 1289774947',
                'date_of_birth' => fake()->date,
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/customers/9.webp'),
                'user_type' => 'admin',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ];

        if (env('IS_DUMMY_DATA')) {
            foreach ($users as $key => $user_data) {
                $featureImage = $user_data['profile_image'] ?? null;
                $user_data['username'] = $user_data['first_name'] . '_' . $user_data['last_name'];

                // Generate slug if not provided
                if (!isset($user_data['slug'])) {
                    $user_data['slug'] = \Illuminate\Support\Str::slug($user_data['first_name'] . ' ' . $user_data['last_name']);
                }

                $userData = Arr::except($user_data, ['profile_image']);
                $user = User::create($userData);
                if (!empty($userData['user_type'])) {
                    $user->assignRole($userData['user_type']);
                } else {
                    $user->assignRole('user'); // Default role
                }
                event(new UserCreated($user));
                if (isset($featureImage)) {
                    $this->attachFeatureImage($user, $featureImage);
                }
            }
            if (env('IS_FAKE_DATA')) {
                User::factory()->count(30)->create()->each(function ($user) {
                    // Generate slug for factory users
                    $user->slug = \Illuminate\Support\Str::slug($user->first_name . ' ' . $user->last_name);
                    $user->save();

                    $user->assignRole('user');
                    $img = public_path('/dummy-images/customers/' . fake()->numberBetween(1, 13) . '.webp');
                    $this->attachFeatureImage($user, $img);
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function attachFeatureImage($model, $publicPath)
    {
        if (! env('IS_DUMMY_DATA_IMAGE')) {
            return false;
        }

        $file = new \Illuminate\Http\File($publicPath);

        $media = $model->addMedia($file)->preservingOriginal()->toMediaCollection('profile_image');

        return $media;
    }
}
