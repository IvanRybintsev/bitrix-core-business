{"version":3,"sources":["calendar-view-entry-slider.js"],"names":["window","ViewSlider","calendar","this","id","Math","round","random","sliderId","zIndex","DOM","prototype","show","params","entry","formType","util","doBxContextFix","BX","SidePanel","Instance","open","contentCallback","delegate","createContent","events","onDestroy","xhr","abort","bind","onClose","proxy","hide","onCloseComplete","destroy","printable","disableKeyHandler","opened","event","getSliderPage","getUrl","denyClose","denyAction","removeCustomEvent","onCustomEvent","plannerId","enableKeyHandler","userListPopup","close","setTimeout","getView","deselectEntry","restoreBxContextFix","isOpened","slider","top","promise","Promise","ajax","get","getActionUrl","action","unique_id","form_type","sessid","bitrix_sessid","bx_event_calendar_request","entry_id","date_from","data","DATE_FROM","section_name","getSectionName","date_from_offset","TZ_OFFSET_FROM","reqId","html","isDestroyed","fulfill","trim","initControls","buttonSet","initPlannerControl","initUserListControl","editButton","delButton","offsetHeight","addClass","entryController","canDo","editEntry","remove","deleteEntry","viewElementBind","showTitle","node","type","isElementNode","getAttribute","getCurrentStatus","initAcceptMeetingControll","sidebarInner","items","querySelectorAll","length","removeClass","copyButton","copyEventUrl","plannerWrap","offsetWidth","timeoutCheck","width","userList","y","i","q","n","isMeeting","getAttendees","forEach","user","STATUS","push","toLowerCase","showUserListPopup","userListPopupWrap","create","props","className","userWrap","appendChild","height","src","AVATAR","href","URL","text","DISPLAY_NAME","PopupWindowManager","autoHide","closeByEsc","offsetTop","offsetLeft","resizable","lightShadow","content","setAngle","offset","addCustomEvent","setStatus","SetStatusButton","wrap","currentStatus","value","changeStatusCallback","setMeetingStatus","url","getEventPath","clipboard","copy","timeoutIds","popupParams","message","darkMode","angle","popup","PopupWindow","timeoutId","pop","clearTimeout","status","updateStatus","selectorButton","click","showPopup","selectorButtonText","selectorButtonIcon","buttonY","buttonI","style","display","buttonN","innerHTML","menuPopup","res","isFunction","popupWindow","isShown","menuItems","onclick","PopupMenu","BXEventCalendar","ViewEntrySlider"],"mappings":"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","file":"calendar-view-entry-slider.map.js"}