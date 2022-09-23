<div class="wrap">           
    <?php 
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        if (isset($_POST['locationsubmitted'])) {
            $location = trim($_POST['location']);
            $address = trim($_POST['address']);
            $postcode = trim($_POST['postcode']);

            $location_sql = $wpdb->prepare("INSERT INTO ".$wpdb->prefix."bt_class_locations (location, address, post_code)
            VALUES (%s, %s, %s)",$location, $address, $postcode);

            $wpdb->query($location_sql);
            //dbDelta($location_sql);
        } elseif (isset($_POST['ctypesubmitted'])) {
            $class_name = trim($_POST['class-name']);
            $minage = trim($_POST['minage']);
            $maxage = trim($_POST['maxage']);

            $ctype_sql = $wpdb->prepare("INSERT INTO ".$wpdb->prefix."bt_class_type (class_name, min_age, max_age)
            VALUES (%s, %s, %s)",$class_name, $minage, $maxage);

            $wpdb->query($ctype_sql);
        } elseif (isset($_POST['classsubmitted'])) {
            $class = trim($_POST['class']);
            $maxstudents = trim($_POST['maxstudents']);
            $day = trim($_POST['day']);
            $time = trim($_POST['time']);
            $location = trim($_POST['location']);

            $class_sql = $wpdb->prepare("INSERT INTO ".$wpdb->prefix."bt_classes (class_type_id, max_students, class_day, class_time, location_id)
            VALUES (%s, %s, %s, %s, %s)", $class, $maxstudents, $day, $time, $location);

            $wpdb->query($class_sql);

        } elseif (isset($_POST['weekssubmitted'])){
            $numberofweeks = trim($_POST['weeks-ahead']);

            $sql = $wpdb->prepare("UPDATE ".$wpdb->prefix."bt_settings SET value=%s WHERE setting='WEEKS_AHEAD'", $numberofweeks);
            $wpdb->query($sql);

        } elseif (isset($_POST['excludedatesubmitted'])){
            $exclude = trim($_POST['exclude']);

            $sql = $wpdb->prepare("INSERT INTO ".$wpdb->prefix."bt_exclude_dates (exclude_date) VALUES (%s)", $exclude);
            
            $wpdb->query($sql);
        } 
        
        //Bring back lists so that they can be used
        //List of locations
        $query = "SELECT * FROM  ".$wpdb->prefix."bt_class_locations";
        $locations_list = $wpdb->get_results ($query);

        //List of class types
        $query = "SELECT * FROM  ".$wpdb->prefix."bt_class_type";
        $class_type_list = $wpdb->get_results ($query);


    ?>
    <div> <!-- classes -->
        <h1>Book Trial</h1>
        <p>Edit when trial session can be booked</p>

        <?php 
            $query = "SELECT value FROM ".$wpdb->prefix."bt_settings WHERE setting='WEEKS_AHEAD'";
            $weeks_ahead = $wpdb->get_var($query);
        ?>

        <form method="POST">
            <p><b>How many weeks in advance can trial be booked: </b></p> 
            <input type="text" name="weeks-ahead" value=<?php echo $weeks_ahead ?>>
            <button type="submit">Update</button>
            <input type="hidden" name="weekssubmitted" id="submitted" value="true" />
        </form>


        <p><b>Current classes</b></p>
        <table class="bt-table"> 
            <tr>
                <th class="bt-th">Class</th>
                <th class="bt-th">Maximum Students</th>
                <th class="bt-th">Minimum Age</th>
                <th class="bt-th">Maximum Age</th>
                <th class="bt-th">Day</th>
                <th class="bt-th">Time</th>
                <th class="bt-th">Location</th>
                <th class="bt-th">Action</th>
            </tr>
            <?php
                $query = "SELECT ct.class_name, c.id as class_id, ct.id as class_type_id, d.day, c.max_students, ct.max_age, ct.min_age, c.class_time, c.class_day, cl.location
                FROM ".$wpdb->prefix."bt_class_type ct
                JOIN  ".$wpdb->prefix."bt_classes c ON ct.id = c.class_type_id
                JOIN  ".$wpdb->prefix."bt_class_locations cl ON cl.id = c.location_id
                JOIN ".$wpdb->prefix."bt_days d ON d.id=c.class_day";

                $class_list = $wpdb->get_results ($query);

                if (count($class_list)>0) {

                    
                    foreach($class_list as $class) {
                        ?>
                        <tr>
                            <td class="bt-td" id="class_name<?php echo $class->class_id?>"><?php echo stripslashes($class->class_name)?></td>
                            <td class="bt-td" id="max_students<?php echo $class->class_id?>"><?php echo $class->max_students?></td>
                            <td class="bt-td" id="min_age<?php echo $class->class_id?>"><?php echo $class->min_age?></td>
                            <td class="bt-td" id="max_age<?php echo $class->class_id?>"><?php echo $class->max_age?></td>
                            <td class="bt-td" id="day<?php echo $class->class_id?>"><?php echo $class->day?></td>
                            <td class="bt-td" id="class_time<?php echo $class->class_id?>"><?php echo $class->class_time?></td>
                            <td class="bt-td" id="location<?php echo $class->class_id?>"><?php echo stripslashes($class->location)?></td>
                            <td class="bt-td">
                            <i class="iconify" data-icon="typcn:tick" id="save_button_class<?php echo $class->class_id?>" value="Save" class="save" style="display:none;" onclick="save_row_class('<?php echo $class->class_id;?>')"></i></a>
                            <i class="iconify" data-icon="el:edit" id="edit_button_class<?php echo $class->class_id?>" value="Edit" class="edit" onclick="edit_row_class('<?php echo $class->class_id;?>')"></i></a>
                            <i class="iconify" data-icon="icomoon-free:bin" id="delete_button_class<?php echo $class->class_id?>" value="Delete" class="delete" onclick="delete_row_class('<?php echo $class->class_id;?>')"></i></a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                else {
                    ?><td><p>No classes to display</p></td><?php
                }
            ?>
        </table>
        <button type="button" class="class_button" onclick="openFormClass()">Add new class</button>
        <div class="class-form-popup" id="myFormClass">
            <form method="POST" class="form-container" name="class_button">
                <label for="class"><b>Class</b></label>
                <select id="class" name="class" required>
                    <?php foreach($class_type_list as $class_type) { ?>
                        <option value=<?php echo $class_type->id ?>><?php echo $class_type->class_name ?></option>
                    <?php } ?>
                </select>

                <label for="maxstudents"><b>Max Students</b></label>
                <input type="number" placeholder="Max Students" name="maxstudents" required>

                <label for="day"><b>Day</b></label>
                <select id="day" name="day" required>
                    <option value=1>Monday</option>
                    <option value=2>Tuesday</option>
                    <option value=3>Wednesday</option>
                    <option value=4>Thursday</option>
                    <option value=5>Friday</option>
                    <option value=6>Saturday</option>
                    <option value=7>Sunday</option>
                </select>

                <label for="time"><b>Time</b></label>
                <input type="text" placeholder="Time" name="time" required>

                <label for="location"><b>Location</b></label>
                <select id="location" name="location" required>
                    <?php foreach($locations_list as $location) { ?>
                        <option value=<?php echo $location->id ?>><?php echo stripslashes($location->location) ?></option>
                    <?php } ?>
                </select>

                <button type="submit">Add Class</button>
                <input type="hidden" name="classsubmitted" id="submitted" value="true" />
                <button type="button" class="btn cancel" onclick="closeFormClass()">Close</button>
            </form>
        </div>
    </div><!-- classes -->
    <div> <!-- locations -->
    <p><b>Locations</b></p>
        <table class="bt-table">
            <tr>
                <th class="bt-th">Location</th>
                <th class="bt-th">Address</th>
                <th class="bt-th">Post Code</th>
            </tr>
            <?php
                if (count($locations_list)>0) {

                    foreach($locations_list as $location) {
                        ?>
                        <tr>
                            <td class="bt-td" id="location<?php echo $location->id?>"><?php echo stripslashes($location->location)?></td>
                            <td class="bt-td" id="address<?php echo $location->id?>"><?php echo $location->address?></td>
                            <td class="bt-td" id="postcode<?php echo $location->id?>"><?php echo $location->post_code?></td>
                            <td class="bt-td">
                            <i class="iconify" data-icon="typcn:tick" id="save_button_location<?php echo $location->id?>" value="Save" class="save" style="display:none;" onclick="save_row_location('<?php echo $location->id;?>')"></i></a>
                            <i class="iconify" data-icon="el:edit" id="edit_button_location<?php echo $location->id?>" value="Edit" class="edit" onclick="edit_row_location('<?php echo $location->id;?>')"></i></a>
                            <i class="iconify" data-icon="icomoon-free:bin" value="Delete" class="delete" id="delete_button_location<?php echo $location->id?>" onclick="delete_row_location('<?php echo $location->id;?>')"></i></a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                else {
                    ?><td class="bt-td"><p>No locations to display</p></td><?php
                }

            ?>
        </table>
        <button type="button" class="location_button" onclick="openFormLocation()">Add new location</button>
        <div class="location-form-popup" id="myFormLocation">
            <form method="POST" class="form-container" name="location_button">

                <label for="location"><b>Location</b></label>
                <input type="text" placeholder="Enter Location" name="location" required>

                <label for="address"><b>Address</b></label>
                <input type="text" placeholder="Enter Address" name="address" required>

                <label for="postcode"><b>Post Code</b></label>
                <input type="text" placeholder="Enter Post Code" name="postcode" required>

                <button type="submit">Add Location</button>
                <input type="hidden" name="locationsubmitted" id="submitted" value="true" />
                <button type="button" class="btn cancel" onclick="closeFormLocation()">Close</button>
            </form>
        </div>
    </div><!-- locations -->
    <div> <!-- class types -->

        <p><b>Class Types</b></p>
        <table class="bt-table">
            <tr>
                <th class="bt-th">Class Name</th>
                <th class="bt-th">Minimum Age</th>
                <th class="bt-th">Maximum Age</th>
            </tr>
            <?php
                if (count($class_type_list)>0) {
                    foreach($class_type_list as $class_type) {
                        ?>
                        <tr>
                            <td class="bt-td" id="class_name<?php echo $class_type->id?>"><?php echo stripslashes($class_type->class_name)?></td>
                            <td class="bt-td" id="min_age<?php echo $class_type->id?>"><?php echo $class_type->min_age?></td>
                            <td class="bt-td" id="max_age<?php echo $class_type->id?>"><?php echo $class_type->max_age?></td>
                            <td class="bt-td">
                            <i class="iconify" data-icon="typcn:tick" id="save_button_class_type<?php echo $class_type->id?>" value="Save" class="save" style="display:none;" onclick="save_row_class_type('<?php echo $class_type->id;?>')"></i></a>
                            <i class="iconify" data-icon="el:edit" id="edit_button_class_type<?php echo $class_type->id?>" value="Edit" class="edit" onclick="edit_row_class_type('<?php echo $class_type->id;?>')"></i></a>
                            <i class="iconify" data-icon="icomoon-free:bin" id="delete_button_class_type<?php echo $class_type->id?>" value="Delete" class="delete" onclick="delete_row_class_type('<?php echo $class_type->id;?>')"></i></a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                else {
                    ?><td class="bt-td"><p>No class types to display</p></td><?php
                }

            ?>
        </table>
        <button type="button" class="classtype_button" onclick="openFormCType()">Add new class type</button>
        <div class="ctype-form-popup" id="myFormCType">
            <form method="POST" class="form-container" name="classtype_button">

                <label for="class-name"><b>Class Name</b></label>
                <input type="text" placeholder="Enter Class Name" name="class-name" required>

                <label for="minage"><b>Minimum Age</b></label>
                <input type="number" placeholder="Enter Minimum Age" name="minage" required>

                <label for="maxage"><b>Maximum Age</b></label>
                <input type="number" placeholder="Enter Maximum Age" name="maxage" required>

                <button type="submit">Add Class Type</button>
                <input type="hidden" name="ctypesubmitted" id="submitted" value="true" />
                <button type="button" class="btn cancel" onclick="closeFormCType()">Close</button>
            </form>
        </div>
    </div><!-- class types -->

    <div> <!-- dates to exclude -->

        <p><b>Dates to exclude</b></p>
        <?php //List of class types
        $query = "SELECT * FROM  ".$wpdb->prefix."bt_exclude_dates WHERE exclude_date >= curdate()";
        $exclude_dates_list = $wpdb->get_results ($query);?>
        <table class="bt-table-date">
            <tr>
                <th class="bt-th">Date</th>
            </tr>
            <?php
                if (count($exclude_dates_list)>0) {
                    foreach($exclude_dates_list as $date) {
                        ?>
                        <tr>
                            <td class="bt-td"><?php echo $date->exclude_date?></td>
                            <td class="bt-td"><i class="iconify" data-icon="icomoon-free:bin" value="Delete" class="delete" onclick="delete_row_date('<?php echo $date->id;?>')"></i></a></td>
                        </tr>
                        <?php
                    }
                }
                else {
                    ?><td class="bt-td"><p>No excluded dates to display</p></td class="bt-td"><?php
                }

            ?>
        </table>
        <button type="button" class="excludetype_button" onclick="openFormExclude()">Add new date to exclude</button>
        <div class="exclude-form-popup" id="myFormExclude">
            <form method="POST" class="form-container" name="classtype_button">

                <label for="exclude"><b>Date to exclude</b></label>
                <input type="date" name="exclude" required>

                <button type="submit">Add Date</button>
                <input type="hidden" name="excludedatesubmitted" id="submitted" value="true" />
                <button type="button" class="btn cancel" onclick="closeFormExclude()">Close</button>
            </form>
        </div>
    </div><!-- dates to exclude -->
</div>