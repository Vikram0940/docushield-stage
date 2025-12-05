CKEDITOR.plugins.add('dsmenus', {
  requires: 'menubutton,menu',
  init: function (editor) {
    /* ========= FILE ========= */
    editor.addMenuGroup('fileGroup');
    editor.addMenuItem('newDoc', { label: 'New', command: 'newDoc', group: 'fileGroup' });
    editor.addMenuItem('saveDoc', { label: 'Save', command: 'saveDoc', group: 'fileGroup' });
    editor.addCommand('newDoc', { exec: ed => ed.setData('') });
    editor.addCommand('saveDoc', { exec: ed => alert('Saving:\n\n' + ed.getData()) });
    editor.ui.add('FileMenu', CKEDITOR.UI_MENUBUTTON, {
      label: 'File',
      onMenu: () => ({ newDoc: CKEDITOR.TRISTATE_OFF, saveDoc: CKEDITOR.TRISTATE_OFF })
    });

    /* ========= EDIT ========= */
    editor.addMenuGroup('editGroup');
    editor.addMenuItem('undo', { label: 'Undo', command: 'undo', group: 'editGroup' });
    editor.addMenuItem('redo', { label: 'Redo', command: 'redo', group: 'editGroup' });
    editor.ui.add('EditMenu', CKEDITOR.UI_MENUBUTTON, {
      label: 'Edit',
      onMenu: () => ({
        undo: editor.getCommand('undo').state,
        redo: editor.getCommand('redo').state
      })
    });

    /* ========= VIEW ========= */
    editor.addMenuGroup('viewGroup');
    editor.addMenuItem('fullscreen', { label: 'Fullscreen', command: 'maximize', group: 'viewGroup' });
    editor.addMenuItem('sourceView', { label: 'Source', command: 'source', group: 'viewGroup' });
    editor.ui.add('ViewMenu', CKEDITOR.UI_MENUBUTTON, {
      label: 'View',
      onMenu: () => ({ fullscreen: CKEDITOR.TRISTATE_OFF, sourceView: CKEDITOR.TRISTATE_OFF })
    });

    /* ========= HELP ========= */
    editor.addMenuGroup('helpGroup');
    editor.addMenuItem('aboutApp', { label: 'About', command: 'aboutApp', group: 'helpGroup' });
    editor.addCommand('aboutApp', { exec: () => alert('DocuShield Editor v1.0 — CKEditor 4.22.1') });
    editor.ui.add('HelpMenu', CKEDITOR.UI_MENUBUTTON, {
      label: 'Help',
      onMenu: () => ({ aboutApp: CKEDITOR.TRISTATE_OFF })
    });
  }
});
