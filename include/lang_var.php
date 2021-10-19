<?php

class LangVar extends LocalObject
{
    var $_lang_code;
    var $_type;
    var $_module;
    var $_template;
    var $_tag_name;
    var $_value;

    /**
     * Constructor
     */
    function LangVar($langCode, $type, $module, $template, $tagName, $value = "")
    {
        $this->_lang_code = $langCode;
        $this->_type = $type;
        $this->_module = $module;
        $this->_template = $template;
        $this->_tag_name = $tagName;
        $this->_value = $value;
    }

    public function GetUpdateQuery()
    {
        $query = "UPDATE language_variable
                    SET value=" . Connection::GetSQLString($this->_value) . "
                    WHERE tag_name=" . Connection::GetSQLString($this->_tag_name) . "
                        AND type=" . Connection::GetSQLString($this->_type) . "
                        AND module=" . Connection::GetSQLString($this->_module) . "
                        AND template=" . Connection::GetSQLString($this->_template) . "
                        AND language_code=" . Connection::GetSQLString($this->_lang_code);
        return $query;
    }

    public function GetInsertQuery()
    {
        $query = "INSERT INTO language_variable (variable_id, tag_name, value, type, module, template, language_code) VALUES (
                    nextval('\"language_variable_variable_id_seq\"'::regclass),
                    " . Connection::GetSQLString($this->_tag_name) . ",
                    " . Connection::GetSQLString($this->_value) . ",
                    " . Connection::GetSQLString($this->_type) . ",
                    " . Connection::GetSQLString($this->_module) . ",
                    " . Connection::GetSQLString($this->_template) . ",
                    " . Connection::GetSQLString($this->_lang_code) . ");";

        return $query;
    }

    public function GetDeleteQuery()
    {
        $query = "DELETE FROM language_variable
                    WHERE tag_name=" . Connection::GetSQLString($this->_tag_name) . "
                        AND type=" . Connection::GetSQLString($this->_type) . "
                        AND module=" . Connection::GetSQLString($this->_module) . "
                        AND template=" . Connection::GetSQLString($this->_template) . "
                        AND language_code=" . Connection::GetSQLString($this->_lang_code);

        return $query;
    }
}

?>