{"version":3,"sources":["script.js"],"names":["exports","main_core","main_core_events","ui_notification","_templateObject7","data","babelHelpers","taggedTemplateLiteral","_templateObject6","_templateObject5","_templateObject4","_templateObject3","_templateObject2","_templateObject","_createForOfIteratorHelper","o","allowArrayLike","it","Symbol","iterator","Array","isArray","_unsupportedIterableToArray","length","i","F","s","n","done","value","e","_e","f","TypeError","normalCompletion","didErr","err","step","next","_e2","return","minLen","_arrayLikeToArray","Object","prototype","toString","call","slice","constructor","name","from","test","arr","len","arr2","_classPrivateMethodGet","receiver","privateSet","fn","has","namespace","Reflection","toJsonString","JSON","stringify","v","_getRobotsTemplate","WeakSet","_activateSection","_validateScriptName","_validateConstants","ScriptEditComponent","options","classCallCheck","this","add","defineProperty","Type","isPlainObject","baseNode","leftMenuNode","saveButtonNode","formNode","documentType","signedParameters","saveCallback","automationDesigner","BX","Bizproc","Automation","Designer","component","createClass","key","init","_this","Event","bind","saveHandler","initMenu","scriptNameNode","elements","NAME","isStringFilled","Dom","addClass","closest","removeClass","EventEmitter","subscribe","configsMenuItem","addNoticeIcon","_this2","form","FormData","scriptFields","_iterator","entries","_step","field","_validateScriptName2","robotsTemplate","_getRobotsTemplate2","setTemplateValues","_validateConstants2","getConstants","collectUsages","Constant","ajax","runComponentAction","analyticsLabel","ID","script","serialize","then","result","isFunction","_this3","querySelectorAll","forEach","el","menuActivateHandler","getAttribute","UI","DropdownMenuItem","getItemByNode","page","_this4","hasClass","showConfigsHandler","configsNode","_this5","clean","constants","parameters","getParameters","robotNodes","robots","robot","node","renderRobotConfigBlock","push","append","Tag","render","Loc","getMessage","_this6","usages","itemNodes","size","Parameter","constId","constant","find","c","Id","renderPropertyBlock","constantPrefix","paramId","parameter","p","parameterPrefix","Text","encode","getTitle","property","prefix","control","FieldType","renderControl","Default","Name","Description","changePropertyDescription","event","_this7","element","currentTarget","wrapper","previousElementSibling","hide","inputElement","focus","applyNewDescription","text","trim","textContent","show","updateConstant","updateParameter","keyCode","unbind","template","_this8","querySelector","setConstantValue","get","param","setParameterValue","templateManager","templates","_activateSection2","section","menuItem","concat","setActiveHandler","Notification","Center","notify","content","usedConstants","window"],"mappings":"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","file":"script.map.js"}