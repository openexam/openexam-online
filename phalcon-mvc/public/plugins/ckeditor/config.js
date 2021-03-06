/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'editing', groups: ['find', 'selection']},
        {name: 'insert'},
        {name: 'forms'},
        {name: 'tools'},
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'others'},
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
        {name: 'styles'},
        {name: 'colors'}
    ];

    config.ToolbarStartExpanded = false;

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';

    // Don't load these plugins:
    config.removePlugins = 'elementspath,texzilla,image,save,font,pastefromword,sourcearea,eqneditor,scayt,wsc';

    // Always load these plugins:
    config.extraPlugins = 'confighelper,wordcount,notification,maximize';

    // Display notifications for 2 sec:
    config.notification_duration = 2000;

    // 
    // Load some nice looking skin:
    // 
    //    config.skin = 'office2013';
    //    config.skin = 'minimalist';
    //    config.skin = 'moono-dark';
    //    config.skin = 'moono';
    //    config.skin = 'moonocolor';
    // 
    config.skin = 'moono_blue';

    config.baseFloatZIndex = 9000;

    config.autoGrow_onStartup = false;

    config.resize_enabled = true;

    config.autoParagraph = false;

    config.enterMode = CKEDITOR.ENTER_BR;

    config.entities = false;

    config.mathJaxLib = baseURL + '/plugins/mathjax/MathJax.js?config=TeX-AMS_HTML';
};
