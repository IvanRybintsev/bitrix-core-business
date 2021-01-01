{"version":3,"sources":["desktop.js"],"names":["window","BX","desktop","Desktop","this","apiReady","clientVersion","disableLogin","autorun","lastSetIcon","currentIcon","showNotifyId","htmlWrapperHead","topmostWindow","topmostWindowTimeout","path","mainUserOptions","pathToAjax","pathToPull","tabItems","tabRedrawTimeout","syncStatus","syncPauseBlock","inited","sizeInited","minWidth","minHeight","timeoutDelayOfLogout","eventHandlers","addCustomEvent","delegate","terminate","reason","logout","bind","e","keyCode","ctrlKey","metaKey","shiftKey","BXIM","setLocalConfig","console","log","windowReload","openDeveloperTools","openLogsFolder","prototype","init","params","ready","message","replace","getApiVersion","enableInVersion","debugEnable","browser","IsMac","document","head","insertBefore","create","attrs","type","html","firstChild","addClass","body","setWindowResizable","setWindowMinSize","Width","MessengerWindow","Height","userInfo","getUserInfo","contentMenu","content","onCustomEvent","newRevision","oldRevision","PULL","closeConfirm","location","reload","error","code","setIconStatus","login","sendErrorCode","textError","userEmail","messenger","connectionStatus","tabId","changeTab","setTimeout","finalizeTrayMenu","onSyncStatusChanged","userOptions","setAjaxPath","status","settings","preventShutdown","notSupported","setWindowSize","setWindowTitle","updateContent","props","className","children","events","click","checkUpdate","innerHTML","appendChild","hideLoader","remove","getBackgroundImage","id","source","BXDesktopSystem","QuerySettings","setBackgroundImage","url","URL","origin","href","promise","Promise","StoreSettings","resolve","getCurrentUrl","protocol","hostname","port","diskReady","callback","success","sessid","bitrix_sessid","loginSuccessCallback","Login","showLoginForm","Logout","skipCheck","ajax","method","dataType","timeout","data","IM_DESKTOP_LOGOUT","onsuccess","Shutdown","onfailure","openBrowser","ExecuteCommand","NotifyNoUpdates","ShowNotifications","browse","full","GetProperty","join","version","parseInt","eventName","eventHandler","realHandler","arEventParams","i","detail","push","apply","addEventListener","removeCustomEvents","forEach","removeEventListener","windowTarget","arguments","length","objEventParams","mainWindow","opener","top","BXWindows","name","BXDesktopWindow","DispatchCustomEvent","hasOwnProperty","findWindow","windowIsFocused","openNextTab","NextTab","SetIconStatus","setIconBadge","count","important","SetIconBadge","SetTabBadge","getContextWindow","setIconTooltip","iconTitle","enabled","SetProperty","setWindowClosable","flashIcon","voiced","FlashIcon","getWorkArea","coordinates","GetWorkArea","left","right","bottom","showNotification","notifyId","js","getHtmlPage","adjustSize","width","height","resize","offsetWidth","offsetHeight","syncPause","immediate","BXFileStorage","SyncPause","getSyncStatus","windowCommand","command","setActiveWindow","indexOf","close","trace","openTopmostWindow","bodyClass","closeTopmostWindow","isPopupPageLoaded","isUtfMode","BXInternals","PopupTemplate","jsContent","initImJs","isDomNode","outerHTML","OpenDeveloperTools","OpenLogsFolder","autorunStatus","value","diskAttachStatus","BitrixDisk","clipboardSelected","element","expandToWholeWord","resultText","selectionStart","selectionEnd","tagName","substring","wordStartPosition","substr","search","wordEndPosition","getSelection","toString","range","getRangeAt","cloneContents","div","createElement","util","htmlspecialcharsback","split","text","clipboardCopy","cut","MessengerCommon","clipboardCut","clipboardPaste","execCommand","clipboardDelete","clipboardUndo","clipboardRedo","clipboardReplaceText","positionStart","positionEnd","focus","selectAll","getLocalConfig","def","querySetting","result","JSON","parse","stringify","removeLocalConfig","filename","Log","createWindow","reuse","popup","GetWindow","getWindowTitle","title","trim","setWindowPosition","setWindowName","addTrayMenuItem","AddTrayMenuItem","EndTrayMenuItem","PreventShutdown","diskReportStorageNotification","ReportStorageNotification","diskOpenFolder","OpenFolder","addSeparator","addTab","force","closeTab","setTabBadge","updateTabBadge","badge","setTabContent","isActiveWindow","IsActiveTab","getActiveWindow","ActiveTab","TAB_CP","TAB_B24NET","windowId","SetActiveTabI","SetActiveTab"],"mappings":"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","file":"desktop.map.js"}