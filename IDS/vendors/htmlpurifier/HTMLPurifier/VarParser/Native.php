<?php

/**
 * This variable parser uses PHP's internal code engine. Because it does
 * this, it can represent all inputs; however, it is dangerous and cannot
 * be used by users.
 */
class HTMLPurifier_VarParser_Native extends HTMLPurifier_VarParser
{

    /**
     * @param $var
     * @param $type
     * @param $allow_null
     *
     * @return mixed|null
     * @throws \HTMLPurifier_VarParserException
     */
    protected function parseImplementation($var, $type, $allow_null) {
        return $this->evalExpression($var);
    }

    /**
     * @param $expr
     *
     * @return null
     * @throws \HTMLPurifier_VarParserException
     */
    protected function evalExpression($expr) {
        $var = null;
        $result = eval("\$var = $expr;");
        if ($result === false) {
            throw new HTMLPurifier_VarParserException("Fatal error in evaluated code");
        }
        return $var;
    }

}

// vim: et sw=4 sts=4
