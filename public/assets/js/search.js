const QuickSearch = document.querySelector('.quick-search');
const SearchInput = document.querySelector('.quick-search-input');
const clearBtn = document.querySelector('.quick-search-clear');

SearchInput.addEventListener('focus', () => {
  //if (SearchInput.value.trim() !== "") {
    QuickSearch.classList.add('opened');
  //}
});

SearchInput.addEventListener('input', () => {
  if (SearchInput.value.trim() !== "") {
    QuickSearch.classList.add('opened');
  } else {
    QuickSearch.classList.remove('opened');
  }
});

// Clear input on button click
clearBtn.addEventListener('click', () => {
  SearchInput.value = '';
  QuickSearch.classList.remove('opened');
  SearchInput.focusout();
});

document.addEventListener('click', (e) => {
  // if click is NOT inside search input or dropdown
  if (!e.target.closest('.quick-search-input') && !e.target.closest('.quick-search-dropdown')) {
    QuickSearch.classList.remove('opened');
  }
});

$(document).ready(function() {
    let selectedIndex = -1; // currently selected item index
    function updateSelection(list, index) {
        list.removeClass('active'); // remove existing highlight
        if(index >= 0 && index < list.length) {
            $(list[index]).addClass('active');
        }
    }

    $('#searchInput').on('keyup focus', function(e){
        let query = $(this).val();
        // Combine suggestions and results into one list
        const allItems = $('#suggestionsList li a, #resultsList li a');

        // Handle arrow keys
        if(e.key === "ArrowDown"){
            selectedIndex++;
            if(selectedIndex >= allItems.length) selectedIndex = 0;
            updateSelection(allItems, selectedIndex);
            return;
        } else if(e.key === "ArrowUp"){
            selectedIndex--;
            if(selectedIndex < 0) selectedIndex = allItems.length - 1;
            updateSelection(allItems, selectedIndex);
            return;
        } else if(e.key === "Enter"){
            if(selectedIndex >= 0) {
                window.location = $(allItems[selectedIndex]).attr('href');
            }
            return;
        }

        // Reset selection on normal typing
        selectedIndex = -1;

        //if(query.length > 1){
            $.ajax({
                url: $app_url + "/search",
                method: "POST",
                data: { query: query },
                success: function(response){
                    //let data = JSON.parse(response);

                    // Suggestions
                    let suggestionsHtml = "";
                    let searchURL = $app_url + '/search';
                    response.suggestions.forEach(item => {
                        suggestionsHtml += `
                            <li>
                                <a href="${searchURL}?q=${encodeURIComponent(item)}">
                                    <i class="bi bi-search"></i> ${item}
                                </a>
                            </li>
                        `;
                    });
                    $('#suggestionsList').html(suggestionsHtml);

                    // Results
                    let resultsHtml = "";
                    response.results.forEach(item => {
                        let icon = item.type === "project" ? "bi bi-folder" : "bi bi-file-earmark-pdf";
                        resultsHtml += `
                            <li>
                                <a href="<?= base_url('detail') ?>/${item.type}/${item.id}">
                                    <i class="${icon}"></i>
                                    <div class="item-label">
                                        ${item.name}
                                        <span class="meta">${item.time}</span>
                                    </div>
                                </a>
                            </li>
                        `;
                    });
                    $('#resultsList').html(resultsHtml);

                    // Show dropdown
                    $('#quickSearchDropdown').removeClass('d-none');
                }
            });
        /*} else {
            $('#quickSearchDropdown').addClass('d-none');
        }*/
    });

    // Hide dropdown when clicking outside
    $(document).click(function(e){
        if(!$(e.target).closest('.quick-search-container').length){
            $('#quickSearchDropdown').addClass('d-none');
        }
    });
});


