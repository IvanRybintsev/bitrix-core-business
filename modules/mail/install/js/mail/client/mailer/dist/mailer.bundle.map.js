{"version":3,"sources":["mailer.bundle.js"],"names":["this","BX","Mail","exports","mail_client_filtertoolbar","mail_client_binding","main_core_events","_createForOfIteratorHelper","o","allowArrayLike","it","Symbol","iterator","Array","isArray","_unsupportedIterableToArray","length","i","F","s","n","done","value","e","_e","f","TypeError","normalCompletion","didErr","err","call","step","next","_e2","return","minLen","_arrayLikeToArray","Object","prototype","toString","slice","constructor","name","from","test","arr","len","arr2","_filter","WeakMap","_filterToolbar","_binding","_mailboxId","_startDir","_selectedDirectory","Mailer","_this","config","arguments","undefined","startDir","filterId","mailboxId","syncAvailable","babelHelpers","classCallCheck","set","writable","elements","top","document","getElementsByClassName","_iterator","_step","element","classList","remove","classPrivateFieldSet","Main","filterManager","getById","Home","Counters","setShortcut","setFilterDir","mailCounterWrapper","querySelector","filterToolbar","FilterToolbar","wrapper","filter","classPrivateFieldGet","build","Binding","initButtons","EventEmitter","subscribe","event","_event$getCompatData","getCompatData","_event$getCompatData2","slicedToArray","grid","Grid","getId","dir","getFilterFieldsValues","setDirectory","UI","InfoHelper","show","lock","handler","removeCustomEvent","SidePanel","Instance","close","addCustomEvent","createClass","key","Filter","FilterApi","getApi","setFields","DIR","apply","getFilterToolbar","Client","Event"],"mappings":"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","file":"mailer.bundle.map.js"}