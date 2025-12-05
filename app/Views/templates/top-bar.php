<!-- RIGHT Main column -->
<div class="col-lg-9 col-xl d-flex flex-column min-vh-100 pt-4">
    <!-- top/search bar -->
    <header class="ds-mainbar">
        <div class="container-fluid py-2">
            <div class="d-flex align-items-center gap-2">
                <!-- Mobile: open sidebar -->
                <button class="btn btn-secondary border-1 border-primary d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#dsSidebar"
                        aria-controls="dsSidebar"
                        aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Search -->
                <div class="quick-search position-relative flex-grow-1">
                    <form class="flex-grow-1 ds-search bg-body" role="search" method="get" action="<?=site_url("dpanel/search")?>">
                        <label class="d-flex flex-row border border-primary align-items-center justify-content-start ps-2">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input class="form-control border-0 quick-search-input" type="text" placeholder="Search" aria-label="Search" id="searchInput" name="q" autocomplete="off" />
                            <button type="button" class="btn border-0 bg-transparent p-0 px-2 quick-search-clear">
                                <i class="bi bi-x"></i>
                            </button>
                            <button class="btn btn-outline-secondary border-0 p-2 bg-transparent rounded-0 quick-search-arrow-right" type="submit">
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </label>
                    </form>

                    <!-- Dropdown -->
                    <div class="quick-search-container position-relative">
                        <!-- Dropdown -->
                        <div id="quickSearchDropdown" class="quick-search-dropdown shadow-sm d-none">
                            <div class="quick-search-section">
                                <p class="section-title">Suggestions</p>
                                <ul id="suggestionsList" class="list-unstyled mb-2"></ul>
                            </div>
                            <div class="quick-search-section">
                                <p class="section-title">Results</p>
                                <ul id="resultsList" class="list-unstyled mb-0"></ul>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right actions -->
                <!-- Right actions -->
                <div class="d-none d-md-flex align-items-center gap-2">
                    <?php
                    if (session()->get("dc_roles") == "ROLE_ADMIN") {
                    ?>
                        <button class="btn text-primary btn-secondary border-1 border-primary fw-300 invite-members editproject" data-event="invite" data-geturl="<?=site_url("dpanel/project/get-json-detail/0")?>" data-url="<?=site_url("dpanel/project/invite-members")?>">
                            <i class="ds ds-user-plus"></i> Invite members
                        </button>
                    <?php
                    }
                    ?>

                    <!-- Notifications Center -->
                    <div class="notifications-center position-relative flex-grow-1">
                        <button class="btn btn-secondary border-1 border-primary fw-300" aria-label="Notifications" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="ds ds-bell"></i>
                        </button>

                        <!-- Dropdown -->
                        <div class="notifications dropdown-menu dropdown-menu-end shadow p-0" aria-labelledby="Notifications">
                            
                            <!-- Header -->
                            <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-white">
                                <p class="mb-0 text-secondary text-14">Notifications</p>
                                <a href="#"><i class="ds ds-settings icon-grey"></i></a>
                            </div>

                            <!-- Body (scrollable) -->
                            <div class="flex-grow-1 overflow-auto notifications-body">
                                <ul class="list-unstyled notifications-list mb-0">
                                    <li class="px-3 py-2 d-flex align-items-start">
                                        <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle me-2">
                                        <div class="info">
                                            <p class="text-primary mb-0"><strong class="UserName">John Doe</strong> <span class="EventType">accepted your invitation.</span></p>
                                            <small class="text-secondary EventTime">2 hours ago</small>
                                        </div>
                                        <a href="all-files.html" class="stretched-link"></a>
                                    </li>
                                    <li class="px-3 py-2 d-flex align-items-start">
                                        <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle me-2">
                                        <div class="info">
                                            <p class="text-primary mb-0"><strong class="UserName">John Doe</strong> <span class="EventType">accepted your invitation.</span></p>
                                            <small class="text-secondary EventTime">2 hours ago</small>
                                        </div>
                                        <a href="all-files.html" class="stretched-link"></a>
                                    </li>
                                    <li class="px-3 py-2 d-flex align-items-start">
                                        <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle me-2">
                                        <div class="info">
                                            <p class="text-primary mb-0"><strong class="UserName">John Doe</strong> <span class="EventType">accepted your invitation.</span></p>
                                            <small class="text-secondary EventTime">2 hours ago</small>
                                        </div>
                                        <a href="all-files.html" class="stretched-link"></a>
                                    </li>
                                    <li class="px-3 py-2 d-flex align-items-start">
                                        <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle me-2">
                                        <div class="info">
                                            <p class="text-primary mb-0"><strong class="UserName">John Doe</strong> <span class="EventType">accepted your invitation.</span></p>
                                            <small class="text-secondary EventTime">2 hours ago</small>
                                        </div>
                                        <a href="all-files.html" class="stretched-link"></a>
                                    </li>
                                    <li class="px-3 py-2 d-flex align-items-start">
                                        <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle me-2">
                                        <div class="info">
                                            <p class="text-primary mb-0"><strong class="UserName">John Doe</strong> <span class="EventType">accepted your invitation.</span></p>
                                            <small class="text-secondary EventTime">2 hours ago</small>
                                        </div>
                                        <a href="all-files.html" class="stretched-link"></a>
                                    </li>
                                    
                                </ul>
                            </div>

                            <!-- Footer (sticky) -->
                            <div class="border-top d-flex justify-content-start align-items-center px-3 py-2 highlight-red">
                                <i class="bi bi-exclamation-circle me-1 text-25 me-3"></i>
                                <div class="info d-flex flex-column">
                                    <a href="upgrade-plan.html" class="no-underline">You're out of space! Upgrade your plan now to add more storage.</a>
                                    <small class="text-secondary EventTime">4d ago</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-container">
                        <div class="profile-icon" id="profileBtn">
                            <?php
                            $userInfo = (new \App\Models\Users)->select("id,avatar")->find(session()->get("dc_userid"));
                            ?>
                            <img src="<?=user_avatar($userInfo->avatar)?>" id="topProfileImage">
                        </div>

                        <div class="profile-menu" id="profileMenu">
                            <a href="<?=site_url("dpanel/user/profile")?>">⚙️ Settings</a>
                            <a href="<?=site_url("dpanel/user/password")?>">🔑 Password</a>
                            <hr class="m-0">
                            <a href="<?=site_url("dpanel/logout")?>" class="text-danger"><i class="fa fa-right-from-bracket"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>