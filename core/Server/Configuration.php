<?php


class Configuration
{
    private static array $runtimeSettings = [];
    private static array $runtimeDefines = [];
    private static string $configurationFile = "/environment.json";


    public static function initializeEnvironmentConfiguration()
    {
        if (! file_exists(Functions::RootPath().static::$configurationFile)) {
            die('No configuration file found');
        }

        $configurationFileContent = file_get_contents(Functions::RootPath().static::$configurationFile);
        $configurationData = static::parseConfigurationFile($configurationFileContent);

        static::initializeRuntimeSettings($configurationData['runtimeSettings']);
        
        if ($_SERVER['SERVER_NAME'] == "pocketphp" || isset($_REQUEST['debug'])) {
            ini_set('display_errors', "1");
            ini_set('display_startup_errors', "1");
            error_reporting(E_ALL ^ E_STRICT ^ E_WARNING ^ E_NOTICE);
        }

        static::initializeRuntimeDefines($configurationData['runtimeDefines']);
        static::initializeTemplateDefines($configurationData['templateDefines']);
    }

    private static function parseConfigurationFile($file)
    {
        $configurationFileContent = json_decode($file, true);
        
        foreach ($configurationFileContent as $environment => $environmentData) {
            $environmentData['configuration']['runtimeDefines']['env'] = $environment;
            
            $conditionsTrue = ! in_array(false, array_map(function($condition) {
                eval("\$conditionValue = ({$condition['field']} {$condition['is']} '{$condition['value']}');");
                return $conditionValue;
            }, $environmentData['conditions']));

            if ($conditionsTrue) {
                foreach ($environmentData['configuration']['runtimeDefines'] as $key => $value) {
                    unset($environmentData['configuration']['runtimeDefines'][$key]);
                    $environmentData['configuration']['runtimeDefines'][strtoupper($key)] = $value;
                }

                foreach ($environmentData['configuration']['templateDefines'] as $key => $value) {
                    unset($environmentData['configuration']['templateDefines'][$key]);
                    $environmentData['configuration']['templateDefines'][strtoupper($key)] = $value;
                }

                return $environmentData['configuration'];
            }
        }

        die('Configuration not specified');
    }

    private static function initializeRuntimeSettings($runtimeSettings) {
        foreach ($runtimeSettings as $key => $value) {
            ini_set($key, $value);
        }
    }

    private static function initializeRuntimeDefines($runtimeDefines) {
        foreach ($runtimeDefines as $key => $value) {
            if (is_string($value)) {
                list($globalVariable, $variableField) = Functions::MatchPhpGlobalRegex($value);
                if ($globalVariable && $variableField) {
                    $value = Functions::StringToPhpGlobal($globalVariable)[$variableField];
                }
            }

            define($key, $value);
        }
    }

    private static function initializeTemplateDefines($templateDefines) {
        foreach ($templateDefines as $key => $value) {
            define($key, $value);
        }
    }
}
