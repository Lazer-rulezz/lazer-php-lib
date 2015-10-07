<?php


class Formatter
{
    /**
     * @param \Exception $exception
     * @return string
     */
    public static function formatException(\Exception $exception)
    {
        $traceStr = self::formatTrace( $exception->getTrace() );
        return getmypid() . ' ' . get_class($exception) . ' ' .$exception->getCode() . ':' . $exception->getMessage() . "\n" . $traceStr;
    }

    /**
     * @param array $trace
     * @return string
     */
    public static function formatTrace(array $trace)
    {
        $lines = [];
        foreach ($trace as $num => $call)
        {
            $place = '';
            $argsFormatted = self::formatArgs($call['args']);
            if (!empty($argsFormatted))
            {
                $argsFormatted = ' ' . $argsFormatted . ' ';
            }
            if (!empty($call['line']) && !empty($call['file']))
            {
                $place = '  @ ' . $call['line'] . ' ' . $call['file'];
            }

            $lines[] = '# ' . $num . ' ' . (empty($call['class']) ? '' : $call['class'] . $call['type']) . $call['function'] . '(' . $argsFormatted . ')' . $place;
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param array $argList
     * @param bool $final
     * @return string
     */
    public static function formatArgs($argList, $final = false)
    {
        if (empty($argList) || !is_array($argList))
        {
            return '';
        }

        $argsProcessed = [];
        foreach ($argList as $arg)
        {
            if (is_resource($arg))
            {
                $dumped = (string)$arg;
            }
            else if (is_object($arg))
            {
                $dumped = 'obj:' . get_class($arg);
            }
            else if (is_array($arg))
            {
                if (empty($arg))
                {
                    $dumped = '[]';
                }
                else if ($final)
                {
                    $dumped = count($arg) . ':[]';
                }
                else
                {
                    $dumped = '[' . self::formatArgs($arg, true) . ']';
                }
            }
            else if (is_string($arg))
            {
                $len = mb_strlen($arg);
                $pos = mb_strpos($arg, "\n");
                if ($pos === false || $len < 255)
                {
                    $dumped = "'" . $arg . "'";
                }
                else
                {
                    if ($pos > 255)
                    {
                        $pos = 255;
                    }
                    $dumped = "'" . mb_substr($arg, 0, $pos-1) . "'...";
                }
            }
            else
            {
                $dumped = var_export($arg, true);
            }
            $argsProcessed[] = $dumped;
        }

        return implode(', ', $argsProcessed);
    }

    public static function formatArray(array $arr)
    {
        $elements = [];
        foreach ($arr as $key => $value)
        {
            $elements[] = "$key: $value";
        }

        return '[' . implode(', ', $elements) . ']';
    }
}
