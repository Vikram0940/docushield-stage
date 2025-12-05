<style>
    .team-box {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .team-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 18px rgba(0,0,0,0.15);
    }
    .team-avatar img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #e9ecef;
    }
    .team-info .team-name {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 3px;
    }
    .team-info .team-role {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }
    .social-icons a {
      color: #0d6efd;
      margin-right: 8px;
      font-size: 18px;
      transition: color 0.2s ease;
    }
    .social-icons a:hover {
      color: #0a58ca;
    }

    /* Responsive: Stack on small screens */
    @media (max-width: 575.98px) {
      .team-box {
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
        <div class="inner-subheader mt-2 mb-3">
            <ul class="breadcrumbs">
                <li><a href="<?=site_url("dpanel")?>" class='text-dark' style="text-decoration: none;">Dashboard</a></li>
                <li class="active">My Team Members</li>
            </ul>
            <div class="content-right"></div>
        </div>

        <div class="row g-4 mt-2">
            <?php
            //$usersModel = new \App\Models\Users;
            if (!empty($team)) {
                foreach ($team as $value) {
                    //$user = $usersModel->find($value->user_id);
            ?>
                    <!-- Team Member 1 -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="team-box">
                            <div class="team-avatar">
                                <a href="javascript:void(0);" class="text-dark <?php echo ($value->id!=0) ? "avatar_info" : ""; ?>" title="<?=$value->name?>" data-geturl="<?php echo ($value->id!=0) ? "/user/get-invited-lists/".$value->id : ""; ?>" data-url="<?php echo ($value->id!=0) ? "/user/update-roles/".$value->id : ""; ?>" data-name="<?=$value->name?>" style="text-decoration: none;"><img src="<?=user_avatar($value->avatar)?>" alt="<?=$value->name?>"></a>
                            </div>
                            <div class="team-info">
                                <div class="team-name"><a href="javascript:void(0);" class="text-dark <?php echo ($value->id!=0) ? "avatar_info" : ""; ?>" title="<?=$value->name?>" data-geturl="<?php echo ($value->id!=0) ? "/user/get-invited-lists/".$value->id : ""; ?>" data-url="<?php echo ($value->id!=0) ? "/user/update-roles/".$value->id : ""; ?>" data-name="<?=$value->name?>" style="text-decoration: none;"><?=$value->name?></a></div>
                                <div class="team-role"><?=$value->business_type?></div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            else {
            ?>
                <div class="col-lg-12">
                    <div class="alert alert-danger">
                        Sorry! It seems there are no available members in your team.
                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <?php
                echo view("templates/alert-template");
                ?>
            </div>
        </div>
    </div>
</main>