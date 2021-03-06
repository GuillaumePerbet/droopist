<?php
namespace Ads\Manager;
use \Ads\Database as Database;
use \Ads\Category as Category;

class CategoryManager extends Database
{
    /**
     * @param integer $id id of category to fetch in database
     * @return Category|boolean fetched Category instance on success | false on fail
     */
    public static function get($id){
        try{
            $pdo = self::connect();
            $select = "SELECT id, name, color, image FROM category WHERE id = :id";
            $request = $pdo -> prepare($select);
            $request -> bindValue(':id', $id);
            $request -> execute();
            if ($category = $request->fetch()) {
                return new Category($category);
            } else {
                throw new \InvalidArgumentException("Category not found !");
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return Category[]|boolean all Category instances on success | false on fail
     */
    public static function getAll(){
        try{
            $pdo = self::connect();
            $select = "SELECT id, name, color, image FROM category";
            $request = $pdo -> prepare($select);
            $request -> execute();
            if ($categories = $request->fetchAll()) {
                return (array_map(function($category){
                    return new Category($category);
                }, $categories));
            } else {
                throw new \LengthException("No category found !");
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}