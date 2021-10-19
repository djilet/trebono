<?php

class CommonObject
{

    var $_errors = array();
    var $_messages = array();
    var $_errorFields = array();

    function AddError($message, $module = null, $replacements = array())
    {
        $this->_errors[] = GetTranslation($message, $module, $replacements);
    }

    function GetErrorsAsArray()
    {
        $messages = array();
        foreach ($this->_errors as $message) {
            $messages[] = array("Message" => $message);
        }
        return $messages;
    }

    function GetErrorsAsString($separator = ", ")
    {
        return implode($separator, $this->_errors);
    }

    function HasErrors()
    {
        return count($this->_errors) != 0;
    }

    function AddMessage($message, $module = null, $replacements = array())
    {
        $this->_messages[] = GetTranslation($message, $module, $replacements);
    }

    function GetMessagesAsArray()
    {
        $messages = array();
        foreach ($this->_messages as $message) {
            $messages[] = array("Message" => $message);
        }
        return $messages;
    }

    function GetMessagesAsString($separator = ", ")
    {
        return implode($separator, $this->_messages);
    }

    function HasMessages()
    {
        return count($this->_messages) != 0;
    }

    function AddErrorField($message, $module = null, $replacements = array())
    {
        $this->_errorFields[] = GetTranslation($message, $module, $replacements);
    }

    function GetErrorFieldsAsArray()
    {
        $messages = array();
        foreach ($this->_errorFields as $message) {
            $messages[] = array("Message" => $message);
        }
        return $messages;
    }

    function GetErrorFieldsAsString($separator = ", ")
    {
        return implode($separator, $this->_errorFields);
    }

    function HasErrorFields()
    {
        return count($this->_errorFields) != 0;
    }

    function GetErrors()
    {
        return $this->_errors;
    }

    function GetMessages()
    {
        return $this->_messages;
    }

    function GetErrorFields()
    {
        return $this->_errorFields;
    }

    function LoadErrorsFromObject($object)
    {
        $this->_errors = $object->GetErrors();
    }

    function LoadMessagesFromObject($object)
    {
        $this->_messages = $object->GetMessages();
    }

    function LoadErrorFieldsFromObject($object)
    {
        $this->_errorFields = $object->GetErrorFields();
    }

    function AppendErrorsFromObject($object)
    {
        $this->_errors = array_merge($this->_errors, $object->GetErrors());
    }

    function AppendMessagesFromObject($object)
    {
        $this->_messages = array_merge($this->_messages, $object->GetMessages());
    }

    function AppendErrorFieldsFromObject($object)
    {
        $this->_errorFields = array_merge($this->_errorFields, $object->GetErrorFields());
    }

    function ClearErrors()
    {
        $this->_errors = array();
    }

    function ClearMessages()
    {
        $this->_messages = array();
    }

    function ClearErrorFields()
    {
        $this->_errorFields = array();
    }
}

?>