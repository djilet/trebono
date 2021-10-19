<?php

class Module
{
    var $_moduleList = null;
    var $_handlerClass;

    function Module()
    {
        $this->_moduleList = array();
        $i = 0;
        if (!$dh = opendir(PROJECT_DIR . "module/")) {
            return;
        }

        while (($file = readdir($dh)) !== false) {
            if ($file == "." || $file == ".." || !is_dir(PROJECT_DIR . "module/" . $file)) {
                continue;
            }

            $name = strtolower($file);
            if (!in_array($name, $GLOBALS["AvailableModuleList"])) {
                continue;
            }

            $this->_moduleList[$name]["Folder"] = $name;
            $this->_moduleList[$name]["Class"] = ucfirst($name) . "Handler";
            $this->_moduleList[$name]["AdminClass"] = "Admin" . ucfirst($name) . "Handler";
            $this->_moduleList[$name]["ApiClass"] = "Api" . ucfirst($name) . "Processor";
            $this->_moduleList[$name]["ApiWebClass"] = "ApiWeb" . ucfirst($name) . "Processor";
            $this->_moduleList[$name]["Title"] = GetTranslation("module-title", $name);
            $this->_moduleList[$name]["AdminTitle"] = GetTranslation("module-admin-title", $name);
            $i++;
        }
        closedir($dh);
    }

    function GetModuleList($selected = '', $adminOnly = false, $withConfig = false)
    {
        $mLinks = array();
        $i = 0;
        foreach ($this->_moduleList as $k => $v) {
            $file = PROJECT_DIR . 'module/' . $v['Folder'] . '/admin.php';
            if (($adminOnly != true || !file_exists($file)) && $adminOnly != false) {
                continue;
            }

            $mLinks[$i] = $v;
            $mLinks[$i]['Link'] = 'module.php?load=' . $v['Folder'];
            if ($v['Folder'] == $selected) {
                $mLinks[$i]['Selected'] = true;
            }
            require_once(PROJECT_DIR . 'module/' . $v['Folder'] . '/init.php');
            $data = $GLOBALS['moduleConfig'][$v['Folder']];
            $mLinks[$i]['ColorA'] = $data['ColorA'];
            $mLinks[$i]['ColorI'] = $data['ColorI'];
            if ($withConfig) {
                $mLinks[$i]['Config'] = $data['Config'];
            }
            $i++;
        }

        return $mLinks;
    }

    function ModuleExists($folder)
    {
        return isset($this->_moduleList[$folder]) ? true : false;
    }

    function LoadForPublic($folder, $templateSet, $pathToModule, $pathInsideModule, $header, $pageID, $config)
    {
        if (!$this->_ValidateModule($folder)) {
            return false;
        }

        eval("\$m = new " . $this->_handlerClass . "();");

        if (!method_exists($m, "InitPublic")) {
            return false;
        }

        $m->InitPublic($folder, $templateSet, $pathToModule, $pathInsideModule, $header, $pageID, $config);

        return true;
    }

    function LoadForAdmin($folder, $pageID, $config)
    {
        if (!$this->_ValidateModule($folder)) {
            return false;
        }

        eval("\$m = new " . $this->_handlerClass . "();");

        if (!method_exists($m, "InitAdmin")) {
            return false;
        }

        $m->InitAdmin($folder, $pageID, $config);

        return $m;
    }

    function LoadForHeader($folder)
    {
        if (!$this->_ValidateModule($folder)) {
            return false;
        }

        eval("\$m = new " . $this->_handlerClass . "();");

        return !method_exists($m, "ProcessHeader") ? false : $m->ProcessHeader($folder);
    }

    function LoadModuleMap($folder, $templateSet, $pathToModule, $pageID, $config, $level)
    {
        if (!$this->_ValidateModule($folder)) {
            return false;
        }

        eval("\$m = new " . $this->_handlerClass . "(\$folder, \$templateSet, \$pathToModule, array(), array(), \$pageID, \$config);");

        return !method_exists($m, "LoadMap") ? array() : $m->LoadMap($level);
    }

    function _ValidateModule($folder)
    {
        if (!isset($this->_moduleList[$folder])) {
            ErrorHandler::TriggerError("Module \"" . $folder . "\" doesn't exist", E_USER_WARNING);

            return false;
        }

        $fileName = dirname(__FILE__) . "/../module/" . $folder . "/public.php";
        if (!file_exists($fileName)) {
            ErrorHandler::TriggerError("File \"" . $fileName . "\" doesn't exist", E_USER_WARNING);

            return false;
        }

        require_once($fileName);

        if (!class_exists($this->_moduleList[$folder]["Class"])) {
            ErrorHandler::TriggerError(
                "Class \"" . $this->_moduleList[$folder]["Class"] . "\" is not found",
                E_USER_WARNING
            );

            return false;
        }

        $this->_handlerClass = $this->_moduleList[$folder]["Class"];

        return true;
    }

    function GetApiProcessor($folder)
    {
        if (!$this->_ValidateModuleApi($folder)) {
            return false;
        }

        $m = new $this->_moduleList[$folder]["ApiClass"]($folder);

        return !method_exists($m, "Process") ? false : $m;
    }

    function GetApiWebProcessor($folder)
    {
        if (!$this->_ValidateModuleApiWeb($folder)) {
            return false;
        }

        $m = new $this->_moduleList[$folder]["ApiWebClass"]($folder);

        return !method_exists($m, "Process") ? false : $m;
    }

    function _ValidateModuleApi($folder)
    {
        if (!isset($this->_moduleList[$folder])) {
            ErrorHandler::TriggerError("Module \"" . $folder . "\" doesn't exist", E_USER_WARNING);

            return false;
        }

        $fileName = dirname(__FILE__) . "/../module/" . $folder . "/api.php";
        if (!file_exists($fileName)) {
            ErrorHandler::TriggerError("File \"" . $fileName . "\" doesn't exist", E_USER_WARNING);

            return false;
        }

        require_once($fileName);

        if (!class_exists($this->_moduleList[$folder]["ApiClass"])) {
            ErrorHandler::TriggerError(
                "Class \"" . $this->_moduleList[$folder]["ApiClass"] . "\" is not found",
                E_USER_WARNING
            );

            return false;
        }

        return true;
    }

    function _ValidateModuleApiWeb($folder)
    {
        if (!isset($this->_moduleList[$folder])) {
            ErrorHandler::TriggerError("Module \"" . $folder . "\" doesn't exist", E_USER_WARNING);

            return false;
        }

        $fileName = dirname(__FILE__) . "/../module/" . $folder . "/api_web.php";
        if (!file_exists($fileName)) {
            ErrorHandler::TriggerError("File \"" . $fileName . "\" doesn't exist", E_USER_WARNING);

            return false;
        }

        require_once($fileName);

        if (!class_exists($this->_moduleList[$folder]["ApiWebClass"])) {
            ErrorHandler::TriggerError(
                "Class \"" . $this->_moduleList[$folder]["ApiWebClass"] . "\" is not found",
                E_USER_WARNING
            );

            return false;
        }

        return true;
    }
}
