<?php

class Template {
    var $vars;

    function Template() {
    }

    public static function factory() {
        return new Template();
    }

    /**
     * Clear the templates variables
     */
    function clear() {
        unset($this->vars);
        return $this;
    }

    /**
     * Set a template variable.
     */
    function set($name, $value) {
        $this->vars[$name] = $value;
        return $this;
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param $_p_file string the template file name
     *  (name obfuscated to avoid clashes with template variables names)
     */
    function fetch($_p_file = null) {
        if (!empty($this->vars)) {
            extract($this->vars);      // Extract the vars to local namespace
        }
        ob_start();                    // Start output buffering
        if (is_readable($_p_file)) {
            include($_p_file);                // Include the file
        } else {
            debug('could not find file', $_p_file);
        }
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        return $contents;              // Return the contents
    }
} // Template()
