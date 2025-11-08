<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CheckEmptyBladeFilesTest extends TestCase
{
    /** @test */
    public function it_checks_for_empty_blade_files_in_all_locations()
    {
        $emptyFiles = [];

        $emptyFiles = array_merge($emptyFiles, $this->getEmptyBladeFiles(resource_path('views')));

        $modulePath = base_path('Modules');
        if (File::exists($modulePath)) {
            $modules = File::directories($modulePath);
            foreach ($modules as $module) {
                $moduleViewsPath = $module . '/Resources/views';
                if (File::exists($moduleViewsPath)) {
                    $emptyFiles = array_merge($emptyFiles, $this->getEmptyBladeFiles($moduleViewsPath));
                }
            }
        }


        $this->assertEmpty($emptyFiles, 'The following Blade files are empty: ' . implode(', ', $emptyFiles));
    }


    private function getEmptyBladeFiles($directory)
    {
        $emptyFiles = [];
        $bladeFiles = File::allFiles($directory);

        foreach ($bladeFiles as $file) {
            if ($file->getExtension() === 'php' && $file->getSize() === 0) {
                $emptyFiles[] = $file->getRelativePathname();
            }
        }

        return $emptyFiles;
    }
}
