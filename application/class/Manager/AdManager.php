<?php
namespace Ads\Manager;
use \Ads\Database as Database;
use \Ads\Ad as Ad;
use \Ads\Manager\CategoryManager as CategoryManager;
use \Ads\Manager\UserManager as UserManager;

class AdManager extends Database
{
    /**
     * @param string $user_email user_email to ckeck in database
     * @return boolean|array true if exists, false if not | ["error" => message] on fail
     */
    public static function user_emailExists($user_email){
        try{
            $pdo = self::connect();
            $select = "SELECT id FROM ad WHERE user_email=:user_email"; 
            $request = $pdo -> prepare($select);
            $request -> bindValue(':user_email', $user_email);
            $request -> execute();
            return boolval($request->fetch());
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @param integer $id id of Ad to check validation
     * @return boolean|array true if validated, false if not | ["error" => message] on fail
     */
    public static function isValidated($id){
        try{
            $pdo = self::connect();
            $select = "SELECT validationDate FROM ad WHERE id=:id"; 
            $request = $pdo -> prepare($select);
            $request -> bindValue(':id', $id);
            $request -> execute();
            return boolval($request->fetch()["validationDate"]);
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @param integer $id id of ad to select in database
     * @return Ad|array selected Ad instance on success | ["error" => message] on fail
     */
    public static function get($id){
        try{
            $pdo = self::connect();
            $select = "SELECT ad.id, user_email, user.lastName AS user_lastName, user.firstName AS user_firstName, user.phone AS user_phone, category_id, category.name AS category_name, title, description, creationDate, validationDate, picture FROM ad INNER JOIN user ON user.email=ad.user_email INNER JOIN category ON category.id=ad.category_id WHERE ad.id = :id";
            $request = $pdo -> prepare($select);
            $request -> bindValue(':id', $id);
            $request -> execute();
            if ($ad = $request->fetch()) {
                return new Ad($ad);
            } else {
                throw new \InvalidArgumentException("Ad not found !");
            }
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @return Ad[] array of all selected Ad instances on success | ["error" => message] on fail
     */
    public static function getAllValidated(){
        try{
            $pdo = self::connect();
            $select = "SELECT ad.id, user_email, user.lastName AS user_lastName, user.firstName AS user_firstName, user.phone AS user_phone, category_id, category.name AS category_name, title, description, validationDate, picture FROM ad INNER JOIN user ON user.email=ad.user_email INNER JOIN category ON category.id=ad.category_id WHERE validationDate IS NOT NULL";
            $request = $pdo -> prepare($select);
            $request -> execute();
            if ($ads = $request->fetchAll()) {
                return (array_map(function($ad){
                    return new Ad($ad);
                }, $ads));
            } else {
                throw new \LengthException("No ads found !");
            }
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @param id $id id of ad to delete in database
     * if user have NO OTHER ADS, delete user from user table
     * @return array ["error" => false] on success | ["error" => message] on fail
     */
    public static function delete($id){
        try{
            $pdo = self::connect();
            $user_email = self::get($id)->user_email;
            $delete = "DELETE FROM ad WHERE id = :id";
            $request = $pdo -> prepare($delete);
            $request -> bindValue(':id', $id);
            if ($request -> execute()){
                if (! self::user_emailExists($user_id)){
                    UserManager::deleteUser($user_email);
                }
                return ["error" => false];
            } else {
                throw new \InvalidArgumentException("Ad not found !");
            }
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @param Ad $ad Ad instance to insert in database
     * @return array ["error" => false] on success | ["error" => message] on fail
     */
    public static function insert($ad){
        try{
            $pdo = self::connect();
            $insert = "INSERT INTO ad (user_email, category_id, title, description, creationDate, picture) VALUES (:user_email, :category_id, :title, :description, NOW(), :picture)";
            $request = $pdo -> prepare($insert);
            $request -> bindValue(':user_email', $ad->user_email);
            $request -> bindValue(':category_id', $ad->category_id);
            $request -> bindValue(':title', $ad->title);
            $request -> bindValue(':description', $ad->description);
            $request -> bindValue(':picture', $ad->picture);
            if ($request -> execute()){
                return ["error" => false];
            } else {
                throw new \PDOException("Ad not inserted !");
            }
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @param Ad $ad Ad instance to update in database
     * @return array ["error" => false] on success | ["error" => message] on fail
     */
    public static function update($ad){
        try{
            $pdo = self::connect();
            $update = "UPDATE ad SET category_id=:category_id, title=:title, description=:description, picture=:picture) WHERE id=:id";
            $request = $pdo -> prepare($update);
            $request -> bindValue(':id', $ad->id);
            $request -> bindValue(':category_id', $ad->category_id);
            $request -> bindValue(':title', $ad->title);
            $request -> bindValue(':description', $ad->description);
            $request -> bindValue(':picture', $ad->picture);
            if ($request -> execute()){
                return ["error" => false];
            } else {
                throw new \PDOException("Ad not updated !");
            }
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }

    /**
     * @param integer $id of ad to validate in database
     * @return array ["error" => false] on success | ["error" => message] on fail
     */
    public static function validate($id){
        try{
            $pdo = self::connect();
            $update = "UPDATE ad SET validationDate=NOW() WHERE id=:id";
            $request = $pdo -> prepare($update);
            $request -> bindValue(':id', $id);
            if ($request -> execute()){
                return ["error" => false];
            } else {
                throw new \PDOException("Ad not validated !");
            }
        } catch (\Exception $e) {
            return(["error"=>$e->getMessage()]);
        }
    }
}