<?php

/**
 * Created by PhpStorm.
 * User: M. Irfaan Auhammad
 * Date: 15-Mar-17
 * Time: 9:33 AM
 */
class Footprints
{
    // -- Variable Declarations
    // -- ---------------------
    private $_objConnection;
    private $_footprintId;
    private $_userId;
    private $_parkId;
    private $_dateVisited;
    private $_userStory;
    private $_isPublic;
    private $_createdOn;
    public static $lstImageExtensions = array("png","PNG","jpg","JPG","jpeg","JPEG","gif","GIF","bmp","BMP");


    // -- Public Constructor Function
    public function __construct($objConnection, $userId)
    {
        $this->_objConnection = $objConnection;
        $this->_userId = $userId;
    }


    // -- PUBLIC PROPERTIES
    // -- -----------------
    /**
     * @param mixed $footprintId
     */
    public function setFootprintId($footprintId)
    {
        $this->_footprintId = $footprintId;
    }

    /**
     * @return mixed
     */
    public function getFootprintId()
    {
        return $this->_footprintId;
    }

    /**
     * @param mixed $parkId
     */
    public function setParkId($parkId)
    {
        $this->_parkId = $parkId;
    }

    /**
     * @return mixed
     */
    public function getParkId()
    {
        return $this->_parkId;
    }

    /**
     * @param mixed $dateVisited
     */
    public function setDateVisited($dateVisited)
    {
        $this->_dateVisited = date("Y-m-d",strtotime($dateVisited));
    }

    /**
     * @return mixed
     */
    public function getDateVisited()
    {
        return $this->_dateVisited;
    }

    /**
     * @param mixed $userStory
     */
    public function setUserStory($userStory)
    {
        $this->_userStory = $userStory;
    }

    /**
     * @return mixed
     */
    public function getUserStory()
    {
        return $this->_userStory;
    }

    /**
     * @param mixed $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->_isPublic = $isPublic;
    }

    /**
     * @return mixed
     */
    public function getIsPublic()
    {
        return $this->_isPublic;
    }

    /**
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->_createdOn;
    }

    /**
     * @param mixed $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->_createdOn = $createdOn;
    }


    // -- PUBLIC FUNCTIONS DECLARATION
    // -- ----------------------------
    // -- Function to add a new footprint
    public function AddNewFootprint() {
        // Variables Declaration
        $fRecordAdded = false;
        $this->_createdOn = date('Y-m-d H:i:s'); // get the current date/time

        // Query to add new footprint
        $sQueryAddFootprint = "
                                INSERT INTO footprints (user_id, park_id, date_visited, user_story, is_public, created_on)
                                VALUES (:userId, :parkId, :dateVisited, :userStory, :isPublic, :createdOn);
                              ";
        $objPDOStatement = $this->_objConnection->prepare($sQueryAddFootprint);
        $objPDOStatement->bindValue(':userId', $this->_userId, PDO::PARAM_INT);
        $objPDOStatement->bindValue(':parkId', $this->_parkId, PDO::PARAM_INT);
        $objPDOStatement->bindValue(':dateVisited', $this->_dateVisited);
        $objPDOStatement->bindValue(':userStory', $this->_userStory, PDO::PARAM_STR);
        $objPDOStatement->bindValue(':isPublic', $this->_isPublic, PDO::PARAM_STR);
        $objPDOStatement->bindValue(':createdOn', $this->_createdOn);
        $fRecordAdded = $objPDOStatement->execute();
        $this->_footprintId = $this->_objConnection->lastInsertId();
        return $fRecordAdded;
    }

    // -- Function taking an array of images as input and storing them in the database
    public function SaveFootprintImages($lstImages) {
        // Query to store footprint's image name in DB
        $sQuery = "INSERT INTO footprint_images (footprint_id, image_src) VALUES (:footprintId, :imageName);";
        $objPDOStatement = $this->_objConnection->prepare($sQuery);
        $objPDOStatement->bindValue(':footprintId', $this->_footprintId);

        foreach ($lstImages as $anImage) {
           $objPDOStatement->bindValue(':imageName', $anImage);
           $objPDOStatement->execute();
        }
    }

    // -- Function to retrieve details to display a footprint
    public function GetFootprintsDetails() {
        // Query to select footprints details
        $sQueryFootprints = "
                                    SELECT fp.footprint_id
                                          ,DATE_FORMAT(fp.date_visited, '%M %d, %Y') as date_visited
                                          ,DATE_FORMAT(fp.created_on,'%b %d, %Y %h:%i %p') as created_on
                                          ,fp.is_public
                                          ,fp.user_story
                                          ,ud.user_id
                                          ,ud.image_src
                                          ,RTRIM(concat(IFNULL(ud.first_name,''), ' ', IFNULL(ud.last_name,''))) AS full_name
                                          ,p.id as parkId
                                          ,p.name
                                      FROM footprints fp
                                INNER JOIN user_details ud
                                        ON ud.user_id = fp.user_id
                                INNER JOIN park p
                                        ON p.id = fp.park_id
                                     WHERE fp.user_id = :userId
                                  ORDER BY fp.created_on DESC;
                            ";
        // Prepare and execute query
        $objPDOStatement = $this->_objConnection->prepare($sQueryFootprints);
        $objPDOStatement->bindValue(':userId', $this->_userId);
        $objPDOStatement->execute();
        $lstFootprints = $objPDOStatement->fetchAll(PDO::FETCH_OBJ);
        return $lstFootprints;
    }

    // -- Function to delete a footprint
    public function Delete() {
        $fStatus = false;
        try {
            // -- Queries to delete footprint details
            // -- -----------------------------------
            // Query to remove footprint_images (Inner join ensures user deletes his own footprint ONLY)
            $sQueryDeleteImages = "
                                        DELETE fi
                                          FROM footprint_images fi 
                                    INNER JOIN footprints f 
                                            ON f.footprint_id = fi.footprint_id 
                                         WHERE f.footprint_id = :footprintId 
                                           AND f.user_id = :userId;
                                  ";

            // Query to remove footprint details
            $sQueryDeleteFootprint = "
                                      DELETE f 
                                      FROM footprints f 
                                      WHERE footprint_id = :footprintId 
                                      AND user_id = :user_id
                                     ";

            // -- Prepare statements and bind values
            // -- ----------------------------------
            $objPDOStmtImages = $this->_objConnection->prepare($sQueryDeleteImages);
            $objPDOStmtPrint = $this->_objConnection->prepare($sQueryDeleteFootprint);
            $objPDOStmtImages->bindValue(':footprintId', $this->_footprintId, PDO::PARAM_INT);
            $objPDOStmtImages->bindValue(':userId', $this->_userId, PDO::PARAM_INT);
            $objPDOStmtPrint->bindValue(':footprintId', $this->_footprintId, PDO::PARAM_INT);
            $objPDOStmtPrint->bindValue(':user_id', $this->_userId, PDO::PARAM_INT);

            // -- Execute queries
            $this->_objConnection->beginTransaction();
            $objPDOStmtImages->execute();
            $objPDOStmtPrint->execute();
            $this->_objConnection->commit();
            $fStatus = true;

            // -- Delete the actual files
            $Folder = $this->_userId . '_' . $this->_footprintId;
            self::RecursiveRemoveDirectory("../../static/img/profile/footprints/{$Folder}");

        } catch (PDOException $e) {
            // SQL Exception occured
            $this->_objConnection->rollback();
        }
        return $fStatus;
    }

    // -- Function to fetch details of a footprint for edit
    public function GetAFootprintDetails() {
        // Variable declaration
        $result = array();

        // Queries to select footprint details and images
        $sQueryFootprintDetails = "
                                    SELECT park_id, date_visited, user_story, is_public, created_on
                                      FROM footprints
                                     WHERE user_id = :userId
                                       AND footprint_id = :footprintId;
                                  ";
        $sQueryFootprintImages = "
                                    SELECT image_id, image_src
                                      FROM footprint_images
                                     WHERE footprint_id = :footprintId;
                                 ";

        // Prepare and Execute queries
        $objPDODetails = $this->_objConnection->prepare($sQueryFootprintDetails);
        $objPDOImages = $this->_objConnection->prepare($sQueryFootprintImages);

        $objPDODetails->bindValue(':userId', $this->_userId);
        $objPDODetails->bindValue(':footprintId', $this->_footprintId);
        $objPDOImages->bindValue(':footprintId', $this->_footprintId);

        $objPDODetails->execute();
        $objFootprintDetails = $objPDODetails->fetch(PDO::FETCH_OBJ);
        $objPDOImages->execute();
        $lstImages = $objPDOImages->fetchAll(PDO::FETCH_OBJ);

        // Return objects in array
        $result[] = $objFootprintDetails;
        $result[] = $lstImages;

        // Return array
        return $result;
    }

    // -- Function to update a footprint details
    public function Update() {
        $fStatus = false;
        // Query to update footprint details
        $sQueryUpdateFootprint = "
                                    UPDATE footprints
                                       SET park_id      = :parkId
                                         , date_visited = :dateVisited
                                         , user_story   = :userStory
                                         , is_public    = :isPublic
                                     WHERE footprint_id = :footprintId
                                       AND user_id      = :userId;
                                 ";
        $objPDOStatement = $this->_objConnection->prepare($sQueryUpdateFootprint);
        $objPDOStatement->bindValue(':parkId', $this->_parkId, PDO::PARAM_INT);
        $objPDOStatement->bindValue(':dateVisited', $this->_dateVisited);
        $objPDOStatement->bindValue(':userStory', $this->_userStory, PDO::PARAM_STR);
        $objPDOStatement->bindValue(':isPublic', $this->_isPublic, PDO::PARAM_STR);
        $objPDOStatement->bindValue(':footprintId', $this->_footprintId, PDO::PARAM_INT);
        $objPDOStatement->bindValue(':userId', $this->_userId, PDO::PARAM_INT);
        try {
            $fStatus = $objPDOStatement->execute();
        } catch(PDOException $e) {
            // Error occured while trying to update the footprint details
        }

        // Return operation result
        return $fStatus;
    }


    // -- PUBLIC STATIC FUNCTIONS DECLARATION
    // -- -----------------------------------
    // -- Function taking a list of park details and return constructed HTML
    public static function ConstructFootprintItems($lstFootprints) {
        // Loop and build a wishlist item
        $sResult = "";
        foreach ($lstFootprints as $objFootprint) {
            $sResult .= "<div id=\"f{$objFootprint->footprint_id}\" data-footprintId=\"{$objFootprint->footprint_id}\" class=\"footprint display-group\">";
            $sResult .= "    <div class=\"row\">";
            $sResult .= "        <div class=\"col col-xs-2 col-sm-2 small-profile-pic\"><img src=\"../static/img/profile/users/{$objFootprint->image_src}\" /></div>";
            $sResult .= "        <div class=\"col col-xs-9 col-sm-9\">";
            $sResult .= "            <div>";
            $sResult .= "                <span class=\"footprint__user\">{$objFootprint->full_name}</span> has been to <span class=\"glyphicon glyphicon-tree-deciduous ai-glyphicon\"></span> <a href=\"../park/?id={$objFootprint->parkId}\" alt='View park details' title='Click to view park details'><span class=\"footprint__park\">{$objFootprint->name}</span></a> <span title=\"{$objFootprint->date_visited}\">recently.</span>";
            $sResult .= "            </div>";
            $sResult .= "            <div class=\"footprint__date\">{$objFootprint->created_on}</div>";
            $sResult .= "        </div>";
            $sResult .= "        <div class=\"col col-xs-1 col-sm-1\">";
            $sResult .= "            <span class=\"glyphicon glyphicon-pencil edit-footprint\" data-footprintId=\"{$objFootprint->footprint_id}\" title=\"Edit this footprint\"></span>";
            $sResult .= "            <button type=\"button\" class=\"close delete-footprint\" data-footprintId=\"{$objFootprint->footprint_id}\" data-footElementId=\"f{$objFootprint->footprint_id}\" title=\"Delete this footprint\" aria-label=\"Delete footprint\">";
            $sResult .= "                <span aria-hidden=\"true\">&times;</span>";
            $sResult .= "            </button>";
            $sResult .= "        </div>";
            $sResult .= "    </div>";
            $sResult .= "    <p class=\"footprint__caption\">{$objFootprint->user_story}</p>";
            $sResult .= "    <div class=\"row footprint__gallery\">";
            // -- Construct a list of pictures for the footprint
            // -- ----------------------------------------------
            // Target directory containing footprint images
            $sFolderPath = '../static/img/profile/footprints/' . $objFootprint->user_id . '_' . $objFootprint->footprint_id . '/';
            $iNbFiles = glob($sFolderPath . "*.{JPG,jpg,jpeg,JPEG,gif,GIF,png,PNG,bmp,BMP}", GLOB_BRACE);   // Number of images in folder
            if (is_dir($sFolderPath)) {    // Only if directory exists
                $sCurrentDirectory = opendir($sFolderPath); // Open folder to read
                if($iNbFiles > 0) {
                    $sResult .= "        <div class=\"owl-carousel owl-theme\">";
                    while(false !== ($file = readdir($sCurrentDirectory)))
                    {
                        $file_path = $sFolderPath.$file;
                        $extension = strtolower(pathinfo($file ,PATHINFO_EXTENSION));
                        if(in_array($extension, Footprints::$lstImageExtensions))
                        {
                            $sResult .= "        <div class=\"item\"><img src=\"$file_path\" /></div>";
                        }
                    }
                    $sResult .= "        </div>";
                }
                closedir($sCurrentDirectory);   // Close folder after read
            }
            $sResult .= "    </div>";
            $sResult .= "</div>";
        }
        return $sResult;
    }

    // -- Function to delete
    public static function RecursiveRemoveDirectory($sPathToDirectory) {
        // Loop through the main directory to delete all files and folders
        foreach(glob("{$sPathToDirectory}/*") as $file) {
            if(is_dir($file)) {
                self::RecursiveRemoveDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($sPathToDirectory);
    }

}