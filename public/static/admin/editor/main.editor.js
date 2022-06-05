(function($, main) {
  var def = {
    UEDITOR_HOME_URL: '', // UEditor URL
    serverUrl: '',				// UEditor内置上传地址设为空
    autoHeightEnabled: false,	// 关闭自动调整高度
    wordCount: false,			// 关闭字数统计
    toolbars: [['fullscreen', 'source', '|',  // 自定义工具栏按钮
     'undo', 'redo', '|',  'bold', 'italic', 'underline', 'strikethrough', 
     'forecolor', 'backcolor', 'fontfamily', 'fontsize', 'paragraph', 'link',
     'blockquote', 'insertorderedlist', 'insertunorderedlist', '|',
     'inserttable', 'insertrow', 'insertcol', '|', 'drafts']]
  };
  var instances = {};
  main.editor = function(obj, id, before, ready) {
    var opt = $.extend(true, {}, def);
    before(opt);
    if (instances[id]) {
      instances[id].destroy();
      $('#' + id).removeAttr('id');
    }
    return instances[id] = createEditor(obj, id, opt, ready);
  };
  function createEditor(obj, id, opt, ready) {
    obj.attr('id', id);
    //实例化编辑器到指定id的dom容器上
    var editor = UE.getEditor(id, opt);
    editor.ready(function() {
      ready(editor);
    });
    return editor;
  }
}(jQuery, main));