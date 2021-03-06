<?php
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Class ilObjUdfEditorAccess
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjUdfEditorAccess extends ilObjectPluginAccess
{

    /**
     * @var ilObjUdfEditorAccess
     */
    protected static $instance = null;


    /**
     * @return ilObjUdfEditorAccess
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @var ilAccessHandler
     */
    protected $access;
    /**
     * @var ilObjUser
     */
    protected $usr;


    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->usr = $DIC->user();
    }


    /**
     * @param string   $a_cmd
     * @param string   $a_permission
     * @param int|null $a_ref_id
     * @param int|null $a_obj_id
     * @param int|null $a_user_id
     *
     * @return bool
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id = null, $a_obj_id = null, $a_user_id = null)
    {
        if ($a_ref_id === null) {
            $a_ref_id = filter_input(INPUT_GET, "ref_id");
        }

        if ($a_obj_id === null) {
            $a_obj_id = ilObjUdfEditor::_lookupObjectId($a_ref_id);
        }

        if ($a_user_id == null) {
            $a_user_id = $this->usr->getId();
        }

        switch ($a_permission) {
            case "visible":
            case "read":
                return (($this->access->checkAccessOfUser($a_user_id, $a_permission, "", $a_ref_id) && !self::_isOffline($a_obj_id))
                    || $this->access->checkAccessOfUser($a_user_id, "write", "", $a_ref_id));

            case "delete":
                return ($this->access->checkAccessOfUser($a_user_id, "delete", "", $a_ref_id)
                    || $this->access->checkAccessOfUser($a_user_id, "write", "", $a_ref_id));

            case "write":
            case "edit_permission":
            default:
                return $this->access->checkAccessOfUser($a_user_id, $a_permission, "", $a_ref_id);
        }
    }


    /**
     * @param string   $a_cmd
     * @param string   $a_permission
     * @param int|null $a_ref_id
     * @param int|null $a_obj_id
     * @param int|null $a_user_id
     *
     * @return bool
     */
    protected static function checkAccess($a_cmd, $a_permission, $a_ref_id = null, $a_obj_id = null, $a_user_id = null)
    {
        return self::getInstance()->_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id);
    }


    /**
     * @param class|string $class
     * @param string       $cmd
     */
    public static function redirectNonAccess($class, $cmd = "")
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);

        if (is_object($class)) {
            $ctrl->clearParameters($class);
            $ctrl->redirect($class, $cmd);
        } else {
            $ctrl->clearParametersByClass($class);
            $ctrl->redirectByClass($class, $cmd);
        }
    }


    /**
     * @param int|null $ref_id
     *
     * @return bool
     */
    public static function hasVisibleAccess($ref_id = null)
    {
        return self::checkAccess("visible", "visible", $ref_id);
    }


    /**
     * @param int|null $ref_id
     *
     * @return bool
     */
    public static function hasReadAccess($ref_id = null)
    {
        return self::checkAccess("read", "read", $ref_id);
    }


    /**
     * @param int|null $ref_id
     *
     * @return bool
     */
    public static function hasWriteAccess($ref_id = null)
    {
        return self::checkAccess("write", "write", $ref_id);
    }


    /**
     * @param int|null $ref_id
     *
     * @return bool
     */
    public static function hasDeleteAccess($ref_id = null)
    {
        return self::checkAccess("delete", "delete", $ref_id);
    }


    /**
     * @param int|null $ref_id
     *
     * @return bool
     */
    public static function hasEditPermissionAccess($ref_id = null)
    {
        return self::checkAccess("edit_permission", "edit_permission", $ref_id);
    }
}