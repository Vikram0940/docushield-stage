<style>
    .my-profile-pic {
      position: relative;
      width: 130px;
      height: 130px;
      margin: 0 auto 20px;
    }
    .my-profile-pic img {
      width: 130px;
      height: 130px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #dee2e6;
    }
    .my-profile-pic .camera-icon {
      position: absolute;
      bottom: 5px;
      right: 10px;
      background: #0d6efd;
      color: #fff;
      border-radius: 50%;
      padding: 3px 7px;
      cursor: pointer;
      font-size: 14px;
      border: 1px solid #fff;
    }
    .my-profile-pic input[type="file"] {
      display: none;
    }
    .spinner-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 130px;
      height: 130px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 50%;
    }
  </style>
<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
        <div class="inner-subheader mt-2 mb-3">
            <ul class="breadcrumbs">
                <li><a href="<?=site_url("dpanel")?>" class='text-dark' style="text-decoration: none;">Dashboard</a></li>
                <li class="active">Profile Settings</li>
            </ul>
            <div class="content-right"></div>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <?php
                echo view("templates/alert-template");
                $avatar = !empty($user) ? $user->avatar : "";
                ?>
                <div class="card" data-active-step="1">
                    <div class="card-body">
                        <div class="my-profile-container text-center">
                            <!-- Profile Image -->
                            <div class="my-profile-pic">
                                <img src="<?=user_avatar($avatar)?>" id="profileImage" alt="<?=$user->name?>">
                                <label for="imageUpload" class="camera-icon">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" id="imageUpload" accept="image/*">

                                <!-- Spinner Overlay -->
                                <div id="uploadSpinner" class="spinner-overlay d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Uploading...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Update Form -->
                            <?php echo form_open(site_url("dpanel/user/update-profile"), "class='needs-validation' id='frmdata' name='frmdata' novalidate"); ?>
                              <div class="mb-3 text-start">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" placeholder="Enter full name" name="name" value="<?=$user->name?>" required>
                              </div>
                              <div class="mb-3 text-start">
                                <label class="form-label">Company name</label>
                                <input type="text" class="form-control" placeholder="Enter company name" name="company_name" value="<?=$user->company_name?>">
                              </div>
                                <?php
                                $typeResult = [];
                                $businessTypes = getBusinessType();
                                // Let's say you want to find the item where value = "IT Professional"
                                $searchValue = $user->business_type;
                                if (!empty($user->business_type)) {
                                    $typeResult = array_filter($businessTypes, function($item) use ($searchValue) {
                                        return $item['value'] === $searchValue;
                                    });
                                }
                                ?>
                              <div class="row">
                                    <div class="mb-3 text-start col-lg-7">
                                        <label class="form-label">Business type</label>
                                        <select class="form-select" name="business_type" style="border: 1px solid #e0e0e0;" required>
                                            <option value="">Choose type</option>
                                            <?php
                                            $bType = "";
                                            foreach ($businessTypes as $type) {
                                                $selected = ""; $otherSelected = "";
                                                if (!empty($user->business_type)) {
                                                    if (empty($typeResult)) {
                                                        $otherSelected = "selected='selected'";
                                                        $bType = $type["value"];
                                                    }
                                                    else {
                                                        if ($type["value"] == $user->business_type) {
                                                            $selected = "selected='selected'";
                                                            $bType = $type["value"];
                                                        }
                                                    }
                                                }
                                                
                                            ?>
                                                <option value="<?=$type["value"]?>" <?=$selected?> <?=$otherSelected?>><?=$type["name"]?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <input type="text" class="form-control mt-2 <?php echo ($bType=="Other") ? "" : "d-none"; ?>" id="otherBusinessType" name="otherBusinessType" placeholder="Enter your business type" value="<?php echo $user->business_type; ?>">
                                    </div>
                                  <div class="mb-3 text-start col-lg-5">
                                    <label class="form-label">Phone number</label>
                                    <input type="text" class="form-control" placeholder="Enter phone number" name="phone" value="<?=$user->phone?>">
                                  </div>
                              </div>
                              <div class="row">
                                <div class="mb-3 text-start col-lg-6">
                                    <label class="form-label">Country</label>
                                    <?php
                                    $countries = (new \App\Models\Countries)->orderBy("name")->findAll();
                                    ?>
                                    <select name="country" class="form-select" style="border:1px solid #e0e0e0;" required>
                                        <option value="">Choose country</option>
                                        <?php
                                        if (!empty($countries)) {
                                            foreach ($countries as $country) {
                                                $selected = ($country->id==$user->country) ? "selected='selected'" : "";
                                                echo "<option value='".$country->id."' ".$selected.">".$country->name."</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3 text-start col-lg-6">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" placeholder="Enter state" name="state" value="<?=$user->state?>">
                                </div>
                                <div class="mb-3 text-start col-lg-6">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" placeholder="Enter city" name="city" value="<?=$user->city?>">
                                </div>
                                <div class="mb-3 text-start col-lg-6">
                                    <label class="form-label">Postal/Zip Code</label>
                                    <input type="text" class="form-control" placeholder="Enter postal/zip code" name="postal_code" value="<?=$user->postal_code?>">
                                </div>
                              </div>
                              <div class="mb-3 text-start">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" rows="3" name="address" placeholder="Enter address"><?=$user->address?></textarea>
                              </div>
                              <button type="submit" class="btn btn-primary w-100" id="btnsubmit">Update Profile Information</button>
                            <?=form_close()?>
                          </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>

</script>