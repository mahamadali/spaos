<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    /** @test */
    public function all_blade_files_should_have_translations()
    {
        $bladeFiles = array_merge(
            File::allFiles(resource_path('views')),  
            File::allFiles(base_path('Modules'))     
        );

        $untranslatedTexts = [];

        foreach ($bladeFiles as $file) {
            if (!$file->getExtension() === 'php' || !str_contains($file->getFilename(), '.blade.php')) {
                continue;
            }

            $content = File::get($file->getRealPath());

            preg_match_all('/>([^<>@{]{3,})<\/|\'([^\']{3,})\'|\"([^\"]{3,})\"/', $content, $matches);

            foreach ($matches[1] as $match) {
                if ($match && $this->isPlainText($match)) {
                    $untranslatedTexts[$file->getRelativePathname()][] = trim($match);
                }
            }
        }

        if (!empty($untranslatedTexts)) {
            $message = "Untranslated text found in Blade files:\n";
            foreach ($untranslatedTexts as $file => $texts) {
                $message .= "$file:\n  - " . implode("\n  - ", $texts) . "\n";
            }
            $this->fail($message);
        }

        $this->assertTrue(true); 
    }

    private function isPlainText($text)
    {
        $text = trim($text);
        if (is_numeric($text) || empty($text)) {
            return false;
        }
        return !preg_match('/(__|@lang)\([\'\"]' . preg_quote($text, '/') . '[\'\"]\)/', $text);
    }
}
