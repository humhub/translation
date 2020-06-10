<?php


namespace humhub\modules\translation\models\parser;


class MessageParser
{
    const PARAMETER_TYPE_NUMBER = 0;
    const PARAMETER_TYPE_DATE = 1;
    const PARAMETER_TYPE_TIME = 2;

    const PARAMETER_TYPE_DEFAULT = self::PARAMETER_TYPE_NUMBER;

    const COMPARE_RESULT_MISSING = -1;
    const COMPARE_RESULT_INVALID = 1;



    public static function parse($message)
    {
        $stateExpression = false;
        $level = 0;
        $expressions = [];
        $expression = '';
        foreach (str_split($message) as $char) {
            if (!$stateExpression && $char !== '{') {
                continue;
            }

            switch ($char) {
                case '{':
                    if (!$stateExpression) {
                        $stateExpression = true;
                    } else {
                        $expression .= $char;
                    }

                    $level++;
                    break;
                case '}':
                    if ($stateExpression) {
                        $level--;
                        if ($level) {
                            $expression .= $char;
                        } else {
                            $stateExpression = false;
                            $expressions[] = $expression;
                            $expression = '';
                        }
                    }
                    break;
                default:
                    if ($stateExpression) {
                        $expression .= $char;
                    }
            }
        }

        $parameters = [];

        foreach ($expressions as $expression) {
            preg_match_all('/([a-zA-Z0-9]+),([a-zA-Z]+)/m', $expression, $messageMatch, PREG_SET_ORDER);

            if(!empty($messageMatch)) {
                $parameters[$messageMatch[0][1]] = static::parseParameterType($messageMatch[0][2]);
            } else {
                $parameters[$expression] = static::PARAMETER_TYPE_DEFAULT;
            }
        }

        return $parameters;
    }

    public static function getDummyData($parameters)
    {
        $result = [];
        foreach ($parameters as $param => $type) {
            $result[$param] = static::getDummyDataByType($type);
        }

        return $result;
    }

    private static function getDummyDataByType($type)
    {
        switch ($type) {
            case static::PARAMETER_TYPE_TIME:
            case static::PARAMETER_TYPE_DATE:
                return time();
            default:
                return 5;

        }
    }

    public static function compareParameter($required, $actual)
    {
        $diff = array_diff_assoc($required, $actual);

        if(!empty($diff)) {
            reset($diff);
            return [key($diff), static::COMPARE_RESULT_MISSING];
        }

        $diff = array_diff_assoc($actual, $required);

        if(!empty($diff)) {
            reset($diff);
            return [key($diff), static::COMPARE_RESULT_INVALID];
        }

        return true;
    }

    const PARAMETER_TYPE_MAPPING = [
        'number' => self::PARAMETER_TYPE_NUMBER,
        'date' => self::PARAMETER_TYPE_DATE,
        'time' => self::PARAMETER_TYPE_TIME,
        'spellout' => self::PARAMETER_TYPE_NUMBER,
        'ordinal' => self::PARAMETER_TYPE_NUMBER,
        'duration' => self::PARAMETER_TYPE_NUMBER,
        'plural' => self::PARAMETER_TYPE_NUMBER,
        'selectordinal' => self::PARAMETER_TYPE_NUMBER,
        'select' => self::PARAMETER_TYPE_NUMBER,
    ];

    private static function parseParameterType($typeStr) {
        if(isset(static::PARAMETER_TYPE_MAPPING[$typeStr])) {
            return static::PARAMETER_TYPE_MAPPING[$typeStr];
        }

        return static::PARAMETER_TYPE_DEFAULT;
    }

}