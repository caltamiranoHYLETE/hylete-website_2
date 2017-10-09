<?php

namespace Weew\Config;

class EnvironmentDetector implements IEnvironmentDetector {
    /**
     * @var array
     */
    protected $rules = [];

    public function __construct() {
        $this->addDefaultRules();
    }

    /**
     * @param $string
     *
     * @return null|string
     */
    public function detectEnvironment($string) {
        foreach ($this->rules as $name => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $string) === 1) {
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param array $abbreviations
     */
    public function addRule($name, array $abbreviations) {
        if (array_get($this->rules, $name) === null) {
            $this->rules[$name] = [];
        }

        $patterns = $this->createPatternsForStrings($abbreviations);

        foreach ($patterns as $pattern) {
            $this->rules[$name][] = $pattern;
        }
    }

    /**
     * Register default environment rules.
     */
    protected function addDefaultRules() {
        $this->addRule('prod', ['prod', 'production']);
        $this->addRule('dev', ['dev', 'development']);
        $this->addRule('test', ['test']);
    }

    /**
     * @param array $strings
     *
     * @return string
     */
    protected function createPatternsForStrings(array $strings) {
        $patterns = [];

        foreach ($strings as $string) {
            $patterns[] = s('#(%s)$#', preg_quote("_$string"));
            $patterns[] = s('#(%s)\.#', preg_quote("_$string"));
            $patterns[] = s('#^(%s)$#', preg_quote("$string"));
        }

        return $patterns;
    }
}
