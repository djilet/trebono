<?php

class ModuleHandler
{
    var $module;
    var $templateSet;
    var $baseURL;
    var $pathInsideModule;
    var $header;
    var $pageID;
    var $config;
    var $content;
    var $tmplPrefix;

    function ModuleHandler()
    {
    }

    function InitPublic($module, $templateSet, $pathToModule, $pathInsideModule, $header, $pageID, $config)
    {
        $this->module = $module;
        $this->templateSet = $templateSet;
        $this->baseURL = GetDirPrefix() . implode("/", $pathToModule);
        $this->pathInsideModule = $pathInsideModule;
        $this->header = array_merge($header, array("PathInsideModule" => implode("/", $pathInsideModule)));
        $this->pageID = $pageID;
        $this->config = $config;
        $this->content = $header['Content'] ?? "";

        $this->tmplPrefix = $this->templateSet ? $this->module . '-' . $this->templateSet . '/' : $this->module . "_";
        $this->ProcessPublic();
    }

    function ProcessPublic()
    {
        return false;
    }

    function InitAdmin($module, $pageID, $config)
    {
        $this->module = $module;
        $this->pageID = $pageID;
        $this->config = $config;
    }

    function RemoveModuleData()
    {
    }

    function ProcessHeader($module)
    {
        return array();
    }

    function IsMainHTML()
    {
        $urlParser =& GetURLParser();
        $chunks = count($this->pathInsideModule);

        return $urlParser->IsHTML() && ($chunks == 0 || ($chunks == 1 && ($this->pathInsideModule[0] == "" || $this->pathInsideModule[0] == INDEX_PAGE . HTML_EXTENSION)));
    }

    function IsMainXML()
    {
        $urlParser =& GetURLParser();
        $chunks = count($this->pathInsideModule);

        //if ($urlParser->IsXML() && ($chunks == 0 || ($chunks == 1 && ($this->pathInsideModule[0] == "" || $this->pathInsideModule[0] == INDEX_PAGE.HTML_EXTENSION))))
        return $urlParser->IsXML() && $chunks == 0;
    }
}