<?php


namespace App\Utils;


use Doctrine\ODM\MongoDB\DocumentManager;

class MultiCascader
{
    private $className;
    private $classShortName;
    private $dm;
    private $associations = array();

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
        $metas = $dm->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $className = $meta->getName();
            $this->associations[$className] = array();
            $associationNames = $dm->getClassMetadata($className)->getAssociationNames();
            foreach ($associationNames as $associationName)
            {
                $this->associations[$className][$associationName] = $dm->getClassMetadata($className)->getAssociationTargetClass($associationName);
            }
        }
    }

    public function getClassShortName($className)
    {
        $splitName = explode("\\", $className);
        return $splitName[count($splitName)-1];
    }

    public function detachAssociations($document)
    {
        $this->className = get_class($document);
        $this->classShortName = $this->getClassShortName($this->className);
        foreach ($this->associations as $className => $associationNames)
        {

            foreach ($associationNames as $associationName => $targetClass) {
                if ($targetClass == $this->className) {
                    // The target entity has an association with the document to detach associations
                    $qb = $this->dm->createQueryBuilder($className)->field($associationName)->references($document);
                    $cursor = $qb->getQuery()->execute();
                    if ($cursor)
                    {
                        foreach ($cursor as $targetObject)
                        {
                            if (!method_exists($targetObject, "remove".$this->classShortName))
                            {
                                $msg = "Method remove".$this->classShortName." not found for association $associationName in targetClass ".get_class($targetObject);
                                echo $msg; die;
                            }
                            else
                            {
                                $removeMethod = "remove".$this->classShortName;
                                $targetObject->$removeMethod($document);
                            }
                        }
                    }
                }
            }
        }
    }
}
