{"version":3,"sources":["stateactivity.js"],"names":["StateActivity","ob","BizProcActivity","Type","Draw","divC","main","appendChild","document","createElement","cellPadding","width","cellSpacing","style","fontSize","textAlign","r","insertRow","id","Name","c","insertCell","background","d111","noWrap","height","th","sp","innerHTML","HTMLEncode","padding","marginLeft","overflow","whiteSpace","title","align","className","onclick","OnSettingsClick","OnRemoveClick","d2","backgroundColor","commandTable","_crt","rows","cells","addlnk","jsUtils","CreateElement","href","ShowAddMenu","textDecoration","BPMESS","i","childActivities","length","rl","cl","ic","cmdT","cmdR","cmdC","clickrow","settings","remove","borderTop","cursor","onmouseover","e","this","onmouseout","reDraw","parentNode","removeChild","parentActivity","RemoveChild","hasOwnProperty","deleteRow","parseInt","DrawLines","Settings","SequentialShow","HideRows","__l","j","display","act","rootActivity","_redrawObject","Table","__header","link","SequentialHide","Properties","spn","__seq","__footer","b","type","value","margin","getElementById","arWorkflowTemplate","Serialize","ReDraw","AddInitialize","CreateActivity","push","AddCommand","act2","AddDelayActivity","AddFinilize","menu","PopupMenu","Create","IsVisible","bStart","bFinish","menuItems","Array","ID","TEXT","ONCLICK","SetItems","BuildItems","pos","GetRealPos","PopupShow","RemoveResources","__StateActivityAdd","BX","isFunction"],"mappings":"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","file":""}