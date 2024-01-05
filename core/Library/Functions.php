<?php


/**
 * Utilities and little wrappers and functions, available globally
 * anywhere in your code. All utility class is purely static, which
 * is a bad thing, but I'm gonna work it out later.
 */
final class Functions
{
    /**
     * Simple debug function
     */
    public static function Debug($errorTitle, $data)
    {
        $debugContainerStyle = "margin: 5px; padding: 5px; border: solid 1px black; border-radius: 5px; background-color: #305eb2";
        $debugTitleStyle = "margin: 5px; padding: 5px; font-family: Verdana; font-size: 15px; color: white";
        $debugDataStyle = "margin: 0px; padding: 0px";
        $debugDataContainerStyle = "margin: 5px; padding: 5px; border: 1px solid black; border-radius: 5px; background-color: #ccc; color: black";
        $debugData = print_r($data, true);

        echo "<div style='{$debugContainerStyle}'>
            <div style='{$debugTitleStyle}'>{$errorTitle}</div>
            <div style='{$debugDataContainerStyle}'>
                <pre style='{$debugDataStyle}'>{$debugData}</pre>
            </div>
        </div>";
    }

    /**
     * Formats absolute and relative OS paths,
     * and returns formatted one for your OS.
     * 
     * @param string $path - path to format
     * 
     * @return string - formatted path.
     */
    public static function OsPath(string $path)
    {
        return str_replace('(\/\\)*', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Simply return the root path.
     */
    public static function RootPath()
    {
        return static::OsPath($_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * Simple returns the path to app.
     */
    public static function AppPath()
    {
        return static::OsPath($_SERVER['DOCUMENT_ROOT'].'/apps');
    }

    /**
     * List of all global variables in PHP.
     * 
     * @return array - list of global variables.
     */
    public static function PhpGlobalsList()
    {
        return [
            '$_SERVER'  => $_SERVER ?? null,
            '$_GET'     => $_GET ?? null,
            '$_POST'    => $_POST ?? null,
            '$_FILES'   => $_FILES ?? null,
            '$_REQUEST' => $_REQUEST ?? null,
            '$_SESSION' => $_SESSION ?? null,
            '$_ENV'     => $_ENV ?? null,
            '$_COOKIE'  => $_COOKIE ?? null,
        ];
    }
    
    /**
     * Regex builder for all global PHP variables.
     * 
     * @return string - regex.
     */
    public static function PhpGlobalRegex()
    {
        return "/".implode('|', array_map(function($globalVariable) {
            return "(\\{$globalVariable})(\['(.*?)'\])";
        }, array_keys(static::PhpGlobalsList())))."/";
    }

    /**
     * Returns result of a regex matching for php global varibles
     * 
     * @param string $string - string to match to.
     * 
     * @return array - global variable and it's field.
     */
    public static function MatchPhpGlobalRegex($string)
    {
        preg_match_all(static::PhpGlobalRegex(), $string, $matches);
        return [$matches[1][0] ?? null, $matches[3][0] ?? null];
    }

    /**
     * Returns global variable based on string representation.
     * 
     * @param string $string - string to match to.
     * 
     * @return array - global variable.
     */
    public static function StringToPhpGlobal($string)
    {
        return static::PhpGlobalsList()[$string];
    }

    /**
     * Redirects user to the specified url.
     * 
     * @param string $url - specified url to redirect to.
     * 
     * @return null
     */
    public static function Redirect(string $url)
    {
        @header("Location: {$url}");
        die("<meta http-equiv='Refresh' content='0; URL={$url}'>");
    }

    /**
     * Escape variable and return it back. If array received,
     * recursively escape all of its values.
     * 
     * @param mixed $variable - variable to escape.
     * 
     * @return mixed - escaped variable.
     */
    public static function EscapeVariable(mixed $var): mixed
    {

        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $var[$key] = escape($value);
            }
        }

        return strip_tags(addslashes(htmlspecialchars($var)));
    }

    /**
     * Wrapper for encoding converter.
     * 
     * @param string $string - string to convert to.
     * @param string $from - encoding to convert from.
     * @param string $to - encoding to convert to.
     * 
     * @return string - converted string.
     */
    public static function ConvertEncoding(string $string, string $from, string $to): string
    {
        return iconv('cp1251', 'utf-8', $string);
    }

    /**
     * Convert different types of line breaks to specified
     * characters (or PHP_EOL, by default).
     * 
     * @param string string - string to convert to.
     * @param string $lineBreak - characters to convert line breaks to.
     * 
     * @return string - string with converted line breaks.
     */
    public static function ConvertLineBreaks(string $string, string $lineBreak=PHP_EOL)
    {
        // Original line breaks to search in string for
        $patterns = [
            "/(<br>|<br \/>|<br\/>)\s*/i",
            "/(\r\n|\r|\n)/"
        ];

        // Line break replacements
        $replacements = [
            PHP_EOL,
            $lineBreak,
        ];


        return preg_replace($patterns, $replacements, $string);
    }

    /**
     * Transliteration for cyrillic characters to english.
     * 
     * @param string $string - string to transliterate.
     * 
     * @return string - transliterated string.
     */
    public static function Transliterate(string $string)
    {
        $characters = mb_str_split(urldecode($string));

        $transliterationMap = [
            ' '=>'_','!'=>'','@'=>'','$'=>'','%'=>'','^'=>'','&'=>'','*'=>'','('=>'',')'=>'',
            '-'=>'_','|'=>'','['=>'',']'=>'','}'=>'','{'=>'','`'=>'','~'=>'','='=>'','а'=>'a',
            'б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i',
            'й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s',
            'т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sh','ъ'=>'',
            'ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya','А'=>'A','Б'=>'B','В'=>'V','Г'=>'G',
            'Д'=>'D','Е'=>'E','Ё'=>'E','Ж'=>'ZH','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L',
            'М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F',
            'Х'=>'H','Ц'=>'C','Ч'=>'CH','Ш'=>'SH','Щ'=>'SH','Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E',
            'Ю'=>'YU','Я'=>'YA','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7',
            '8'=>'8','9'=>'9','0'=>'0','.'=>'.','j'=>'j','q'=>'q','w'=>'w','x'=>'x','Q'=>'Q',
            'W'=>'W','X'=>'X','J'=>'J'
        ];

        $finalString = [];

        $f = '';

        foreach ($characters as $key => $character) {
            if (in_array($character, array_keys($transliterationMap))) {
                $characters[$key] = $transliterationMap[$character];
            }
        }

        $string = implode('', $characters);
        
        return $string;
    }

    /**
     * Converts string from snake to camel case.
     * 
     * @param string $string - string to convert.
     * 
     * @return string - converted string.
     */
    public static function ToCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);

        foreach ($matches[0] as $match) {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }

        return $result;
    }

    /**
     * Returns the directory or file from path.
     * 
     * @param string $path - path from which require file or directory.
     * 
     * @return string|bool $name - name of file or directory or false if path is not readable.
     */
    public static function GetName(string $path) : string|bool
    {
        if (! file_exists($path)) {
            return false;
        }

        $explodedPath = explode('/', $path);

        return end($explodedPath);
    }

    /**
     * Returns all files and directories for the path.
     * 
     * @param string $path - path to walk.
     *
     * @param callable|null $callback - function to execute on every found file.
     * 
     * @return array|null - all directories and files in path.
     */
    public static function Walk(string $path, callable $callback = null) : array|null
    {
        if (! $path) {
            return [];
        }

        $structure = [];

        $scannedDir = scandir($path);

        if (! file_exists($path)) {
            return $structure;
        }

        if (gettype($scannedDir) !== "array") {
            return $structure;
        }

        foreach ($scannedDir as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            }

            $fullFilePath = "{$path}/{$file}";

            if (is_file($fullFilePath)) {
                if (is_callable($callback)) {
                    $callback($fullFilePath);
                }

                $structure[Functions::GetName($fullFilePath)] = $fullFilePath;
            }

            else if (is_dir($fullFilePath)) {
                if (is_callable($callback)) {
                    $callback($fullFilePath);    
                }

                $structure[Functions::GetName($fullFilePath)] = Functions::Walk($fullFilePath, $callback);
            }
        }

        return $structure;
    }

    /**
     * Goes through every element of array (recursively) and applying some function.
     *
     * @param array $array - array to walk through.
     *
     * @param callable|null $callback - function to apply to elements of array.
     *
     * @return void
     */
    public static function WalkArray(array $array, callable $callback = null) : void
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                Functions::WalkArray($value, $callback);
            }

            else if ($callback) {
                $callback($value);
            }
        }
    }

    /**
     * Wrapper for errors.
     *
     * @param string $message - error message to send.
     *
     * @return void
     */
    #[NoReturn] public static function Error(string $message) : void
    {
        die(json_encode([
            "error"     => 1,
            "message"   => $message,
        ]));
    }

    /**
     * Clear scandir (without "." and "..").
     *
     * @param string $directory - directory to scan.
     *`
     * @return array - files in directory.
     */
    public static function ScanDirectory(string $directory) : array
    {
        return array_values(array_filter(scandir($directory), function($file) {
            if (in_array($file, [".", ".."])) {
                return;
            }

            return $file;
        }));
    }
}
