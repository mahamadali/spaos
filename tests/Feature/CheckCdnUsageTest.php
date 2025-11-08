<?php

namespace Tests\Feature;

use Tests\TestCase;

class CheckCdnUsageTest extends TestCase
{
    /**
     * Test to check if any Blade file contains a CDN script or style.
     */
    public function test_blade_files_containing_cdn_links()
    {

        $directories = [
            resource_path('views'), 
            base_path('Modules')    
        ];

        $cdnPattern = '/<(script|link)[^>]+(src|href)="(https?:\/\/(?:cdn\.|cdnjs\.|jsdelivr\.|googleapis\.|bootstrapcdn\.|unpkg\.))[^"]+"/i';

        $filesWithCdn = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue; 
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());

                    if (preg_match($cdnPattern, $content, $matches)) {
                        $filesWithCdn[] = $file->getPathname();
                    }
                }
            }
        }

        if (!empty($filesWithCdn)) {
            $this->fail("The following Blade files contain a CDN link:\n" . implode("\n", $filesWithCdn));
        }

        $this->assertTrue(true);
    }
}
