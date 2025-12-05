const ifiles = [
  {
    name: "Lilo vs. Stitch",
    type: "PDF",
    size: "2 MB",
    itemclass: "pdf",
    icon: "pdf",
    status: "Draft",
    activity: [
      {type: "message", label: "New comments"},
    ],
    teams: [
      {id: 1, title: "Alice Johnson", image: "https://i.pravatar.cc/100?img=1"},
      {id: 2, title: "Bob Singh", image: "https://i.pravatar.cc/100?img=2"},
      {id: 3, title: "Man Singh", image: "https://i.pravatar.cc/100?img=3"},
      {id: 4, title: "Sher Singh", image: "https://i.pravatar.cc/100?img=4"},
      {id: 5, title: "Doland T.", image: "https://i.pravatar.cc/100?img=5"},
    ],
    last_updated: "1 week ago",
    date_added: "January 15, 2025",
  },
  {
    name: "Bilo vs. Stitch",
    type: "Document",
    size: "1 MB",
    itemclass: "document",
    icon: "doc",
    status: "iDraft",
    isFavourite: true, 
    activity: [
      {type: "message", label: "New comments"},
    ],
    teams: [
      {id: 1, title: "Alice Johnson", image: "https://i.pravatar.cc/100?img=1"},
      {id: 2, title: "Bob Singh", image: "https://i.pravatar.cc/100?img=2"},
      {id: 3, title: "Man Singh", image: "https://i.pravatar.cc/100?img=3"},
    ],
    last_updated: "1 day ago",
    date_added: "January 15, 2025",
  },
  {
    name: "Bilo vs. Stitch",
    type: "Image",
    size: "5 MB",
    itemclass: "image",
    icon: "/assets/images/test-item-image.png",
    status: "Draft",
    isFavourite: false, 
    last_updated: "1 day ago",
    date_added: "January 15, 2025",
  },
  {
    name: "Zilo vs. Stitch",
    type: "Document",
    size: "2 MB",
    itemclass: "document",
    icon: "doc",
    status: "Published",
    activity: [
      {type: "message", label: "New comments"},
    ],
    teams: [
      {id: 1, title: "Alice Johnson", image: "https://i.pravatar.cc/100?img=1"},
      {id: 2, title: "Bob Singh", image: "https://i.pravatar.cc/100?img=2"},
    ],
    last_updated: "1 day ago",
    date_added: "January 15, 2025",
  },
];

function FilesListView(ifiles){
    const template = document.getElementById("filesRowTemplate");
    ifiles.forEach(project => {
        const clone = template.content.cloneNode(true);

        clone.querySelector(".item-name").textContent = project.name;
        clone.querySelector(".item-type").textContent = project.type;
        clone.querySelector(".item-size").textContent = project.size;
        
        if(project.isFavourite){
            clone.querySelector(".star").classList.add('starred');
        }
        
        if(project.activity){
            clone.querySelector(".item-activity").innerHTML = project.activity.map(a =>
            ` <span class="activity text-primary">
                <i class="ds ds-${a.type} text-primary"></i> ${a.label}
                </span>`
            ).join(" ");
        }
        
        clone.querySelector(".item-updated").textContent = `${project.last_updated}`;
        clone.querySelector(".item-added").textContent = `${project.date_added}`;

        if(project.teams){
            clone.querySelector(".item-teams").innerHTML = FileTeams(project.teams);
        }
        
        
        // clone.querySelector(".project-link").href = `/projects/${project.name.replace(/\s+/g, '-').toLowerCase()}`;

        listBody.appendChild(clone);
    });
}

function FilesGridView(ifiles) {
  const gridView = document.getElementById("gridView");
  const template = document.getElementById("filesCardTemplate");
  gridView.innerHTML = "";

  ifiles.forEach(project => {
    const clone = template.content.cloneNode(true);
    const ItemClass = project.itemclass;
    const ItemActivity = clone.querySelector(".item-activity");
    const AvatarsAction = clone.querySelector(".avatars-action");
    const ItemStatus = clone.querySelector(".item-status");

    clone.querySelector(".folder").classList.remove('document', 'image', 'pdf');
    clone.querySelector(".folder").classList.add(ItemClass);

    if(project.isFavourite){
        clone.querySelector(".star").classList.add('starred');
    }

    if(project.icon){
        if(ItemClass == "image"){
            clone.querySelector(".mid-icon").style.cssText = `--dsibg: url('${project.icon}');`;
            clone.querySelector(".mid-icon").innerHTML = `<a href="${project.icon}" class="stretched-link file-image-link">&nbsp;</a>`;    
        }else{
            clone.querySelector(".mid-icon").innerHTML = `<i class="ds ds-${project.icon}"></i>`;
        }
    }

    if(project.status){
        ItemStatus.innerHTML = `<span class="activity">${project.status}</span>`;
    }else{
        ItemStatus.remove();
    }
    
    clone.querySelector(".item-name").textContent = project.name;
    
    if(project.activity){
        ItemActivity.innerHTML = project.activity.map(a =>
        ` <span class="activity text-primary">
            ${a.label}
            </span>`
        ).join(" ");
    }else{
        ItemActivity?.remove();
    }

    if(project.teams){
        clone.querySelector(".item-teams").innerHTML = FileTeams(project.teams);
    }else{
        clone.querySelector(".item-teams").remove();
    }
    
    clone.querySelector(".item-updated").textContent = `${project.last_updated}`;

    if(project.itemclass != 'document'){
        AvatarsAction?.remove();
        ItemActivity?.remove();
        ItemStatus?.remove();
    }
    // clone.querySelector(".project-link").href = `/projects/${project.name.replace(/\s+/g, '-').toLowerCase()}`;

    gridView.appendChild(clone);
  });
}

function FileTeams(teams) {
  if (teams.length <= 2) {
    return teams.map(t => 
      `<a href="#" class="avatar" data-bs-toggle="tooltip" title="${t.title}">
        <img src="${t.image}" alt="${t.title}" class="rounded-circle">
      </a>`
    ).join(" ");
  } else {
    const firstTwo = teams.slice(0, 2).map(t => 
      `<a href="#" class="avatar" data-bs-toggle="tooltip" title="${t.title}">
        <img src="${t.image}" alt="${t.title}" class="rounded-circle">
      </a>`
    ).join(" ");
    const moreCount = teams.length - 2;
    return `
      <div class="avatar-group" role="group" aria-label="Shared with">
        ${firstTwo}
        <span class="avatar avatar-more" data-bs-toggle="tooltip" title="${moreCount} more">+${moreCount}</span>
      </div>
    `;
  }
}

document.addEventListener("DOMContentLoaded", () => {
    FilesListView(ifiles);
    FilesGridView(ifiles);

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        if (bootstrap?.Tooltip) new bootstrap.Tooltip(el);
    });

    
    document.querySelectorAll("button.star").forEach(function (star) {
        star.addEventListener("click", function (e) {
            this.classList.toggle("starred");
        });
    });

    document.querySelectorAll(".star").forEach(function (star) {
        star.addEventListener("click", function (e) {
            e.preventDefault();
            this.classList.toggle("starred");
        });
    });
  
    const listBtn = document.getElementById("listViewBtn");
    const gridBtn = document.getElementById("gridViewBtn");
    const listView = document.getElementById("listView");
    const gridView = document.getElementById("gridView");
    
    if(listBtn){
        listBtn.addEventListener("click", () => {
            listView?.classList.remove("d-none");
            gridView?.classList.add("d-none");
            listBtn?.classList.add("active");
            gridBtn?.classList.remove("active");
        });
    }

    if(gridBtn){
        gridBtn.addEventListener("click", () => {
            gridView?.classList.remove("d-none");
            listView?.classList.add("d-none");
            gridBtn?.classList.add("active");
            listBtn?.classList.remove("active");
        });
    }
});