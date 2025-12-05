async function fetchVersionContent(versionId) {
  // Example: Fetch old version content from your API or backend
  // Replace with your actual API route
  const response = await fetch($app_url + '/document/getVersionContent/' + versionId);
  const text = await response.text();
  return text;
}

function showInlineDiff(oldText, newText) {
  // Ensure content is plain text to avoid HTML tag noise
  oldText = oldText.replace(/<[^>]+>/g, '');
  newText = newText.replace(/<[^>]+>/g, '');

  const diffString = Diff.createTwoFilesPatch('Old', 'Current', oldText, newText, '', '');

  console.log('Generated diff:', diffString); // ✅ Check this — it must not be empty

  const targetElement = document.getElementById('diffContainer');
  targetElement.innerHTML = ''; // clear previous diff

  const diff2htmlUi = new Diff2HtmlUI(targetElement, diffString, {
    inputFormat: 'diff',
    outputFormat: 'line-by-line', // can also be 'side-by-side'
    drawFileList: false,
    matching: 'lines',
  });

  diff2htmlUi.draw();
  diff2htmlUi.highlightCode();
}

document.getElementById('leftSelect').addEventListener('change', async function () {
  const selectedVersionId = this.value;

  // Fetch left (old) version
  const oldText = await fetchVersionContent(selectedVersionId);
  console.log(oldText);
  // Get right (current) content from your editor or page
  const newText = document.getElementById('currentContent').innerText.trim();

  // Show inline color diff
  showInlineDiff(oldText, newText);
});
