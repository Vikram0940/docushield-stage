/* ============================================================================
 * Track Changes – Configuration + Modes
 * ============================================================================
 * Usage: editor.config.trackChanges = {
 *   enabled: true,
 *   mode: 'review', // 'off' | 'review' | 'readonly'
 *   author: { id: 'userB', name: 'Reviewer B', avatar: '...' },
 *   ownerCanDecide: true // show Approve/Deny for owner (in review mode)
 * };
 * ============================================================================
 */
window.TrackCfg = {
  isEnabled(editor) {
    const tc = editor && editor.config && editor.config.trackChanges;
    return !!(tc && tc.enabled !== false && tc.mode !== 'off');
  },
  isReadonly(editor) {
    const tc = editor && editor.config && editor.config.trackChanges;
    return !!(tc && tc.mode === 'readonly');
  },
  isReview(editor) {
    const tc = editor && editor.config && editor.config.trackChanges;
    return !!(tc && tc.mode === 'review');
  },
  author(editor) {
    const tc = editor && editor.config && editor.config.trackChanges;
    return (tc && tc.author) || { id: 'anonymous', name: 'Anonymous', avatar: 'https://i.pravatar.cc/40?img=12' };
  }
};

// --- Suggest Mode (toggle at runtime, per-editor) ---------------------------
if (!window.TrackCfg._suggest) window.TrackCfg._suggest = Object.create(null);

window.TrackCfg.isSuggest = function (editor) {
  var id = editor && editor.id;
  return !!(id && window.TrackCfg._suggest[id]);
};

window.TrackCfg.setSuggest = function (editor, on) {
  var id = editor && editor.id;
  if (!id) return;
  window.TrackCfg._suggest[id] = !!on;
  try { localStorage.setItem('tc:suggest:' + id, on ? '1' : '0'); } catch (e) {}
  if (editor) editor.fire('tcSuggestToggled', { on: !!on });
};

window.TrackCfg.getInitialSuggest = function (editor) {
  try { return localStorage.getItem('tc:suggest:' + editor.id) === '1'; }
  catch (e) { return false; }
};
