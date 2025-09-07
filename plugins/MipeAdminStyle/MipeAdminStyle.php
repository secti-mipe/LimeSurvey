<?php
class MipeAdminStyle extends \LimeSurvey\PluginManager\PluginBase {
    protected $storage = 'DbStorage';

    static protected $description = 'Customize the admin theme CSS';
    static protected $name = 'MipeAdminStyle';

    protected $settings = [
        'colorDictionary' => [
            'type' => 'text',
            'label' => 'Dicionário de substituição de cores (JSON)',
            'help'  => 'Inclua um JSON como {"000000":"FF0000","333":"111"}',
            'default' => '{"14AE5C":"337FFF"}',
        ],
        'refreshOverride' => [
            'type' => 'string',
            'label' => 'Forçar atualização',
            'help'  => 'Mude o valor para forçar uma atualização',
            'default' => '0',
        ],
    ];

    public function init() {
        $this->subscribe('beforeAdminMenuRender');
        $this->subscribe('beforeActivate');
    }

    public function saveSettings($settings)
    {
        foreach ($settings as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function beforeAdminMenuRender() {
        // Publish the assets folder and register the CSS file
        $assetUrl = App()->assetManager->publish(dirname(__FILE__) . '/assets/');
        $shouldUpdate = $this->get('refreshOverride', null, null, false) !== '0';
        if ($shouldUpdate) {
            $this->beforeActivate();
            $this->set('refreshOverride', '0');
        }
        App()->clientScript->registerCssFile($assetUrl . '/overrides-custom.css');
        App()->clientScript->registerCssFile($assetUrl . '/custom-admin.css');
    }

    /**
     * Regenerate override CSS only when plugin activated, to avoid overhead.
     */
    public function beforeActivate()
    {
        $assetUrl = dirname(__FILE__) . '/assets';
        $targetFile = $assetUrl . "/overrides-custom.css";

        $colorsDictionary = $this->get('colorDictionary', null, null, false);;
        $dict = json_decode($colorsDictionary, true);

        if (empty($dict) || !is_array($dict)) {
            return;
        }

        $adminCssPath = $assetUrl . "/sea-green.css";
        if (!file_exists($adminCssPath)) {
            return;
        }

        $css = file_get_contents($adminCssPath);
        $modifiedCss = $this->processCssFiles($css, $dict, $targetFile);

        if ($modifiedCss) {
            file_put_contents($targetFile, $modifiedCss);
        }
    }

    private function processCssFiles($cssFile, $colorMappings, $targetFile)
    {
        $customCss = '';

        if (empty($cssFile)) {
            return '';
        }
        
        // Parse CSS content
        $pattern = '/([^{]+)\{([^}]+)\}/';
        preg_match_all($pattern, $cssFile, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $selector = trim($match[1]);
            $properties = trim($match[2]);

            $modifiedProperties = $this->replaceColorsInProperties($properties, $colorMappings);

            if (empty($modifiedProperties)) {
                continue;
            }

            if ($modifiedProperties !== $properties) {
                if(str_starts_with($selector, '}')) {
                    $selector = ltrim($selector, '}');
                }

                $customCss .= "$selector { $modifiedProperties }\n";
            }
        }

        return $customCss;
    }

    private function replaceColorsInProperties($properties, $colorMappings): string
    {
        $propertyLines = explode(';', $properties);
        $modifiedLines = [];

        foreach ($propertyLines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = trim($parts[0]);
            $value = trim($parts[1]);

            // Check if this property might contain colors
            if ($this->isColorProperty($property)) {
                $modifiedValue = $this->replaceColorsInValue($value, $colorMappings);
                if ($modifiedValue !== $value) {
                    $modifiedLines[] = "$property: $modifiedValue;";
                }
            }
        }

        if (empty($modifiedLines)) {
            return '';
        }

        return implode(' ', $modifiedLines);
    }

    private function isColorProperty($property)
    {
        $colorProperties = [
            'color', 'background', 'background-color', 'border-color', 'border-top-color',
            'border-right-color', 'border-bottom-color', 'border-left-color',
            'outline-color', 'text-decoration-color', 'fill', 'stroke', 'box-shadow'
        ];

        return in_array($property, $colorProperties);
    }

    private function replaceColorsInValue($value, $colorMappings)
    {
        $result = $value;
        foreach ($colorMappings as $oldColor => $newColor) {
            // Exact match replacement
            $result = preg_replace('/' . preg_quote($oldColor, '/') . '/i', $newColor, $result);
            
            // Also handle case variations and different formats
            $oldColorVariations = [
                strtolower($oldColor),
                strtoupper($oldColor),
                $this->rgbToHex($oldColor),
                $this->hexToRgb($oldColor)
            ];
            
            foreach ($oldColorVariations as $variation) {
                if ($variation && $variation !== $oldColor) {
                    $result = preg_replace('/' . preg_quote($variation, '/') . '/i', $newColor, $result);
                }
            }
        }
        
        return $result;
    }

    private function rgbToHex($color)
    {
        if (preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $color, $matches)) {
            return sprintf("#%02x%02x%02x", $matches[1], $matches[2], $matches[3]);
        }
        return null;
    }

    private function hexToRgb($color)
    {
        if (preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $color, $matches)) {
            return "rgb(" . hexdec($matches[1]) . ", " . hexdec($matches[2]) . ", " . hexdec($matches[3]) . ")";
        }
        return null;
    }
}