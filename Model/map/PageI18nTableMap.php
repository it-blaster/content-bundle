<?php

namespace Etfostra\ContentBundle\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'page_i18n' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.src.Etfostra.ContentBundle.Model.map
 */
class PageI18nTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.Etfostra.ContentBundle.Model.map.PageI18nTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('page_i18n');
        $this->setPhpName('PageI18n');
        $this->setClassname('Etfostra\\ContentBundle\\Model\\PageI18n');
        $this->setPackage('src.Etfostra.ContentBundle.Model');
        $this->setUseIdGenerator(false);
        // columns
        $this->addForeignPrimaryKey('id', 'Id', 'INTEGER' , 'page', 'id', true, null, null);
        $this->addPrimaryKey('locale', 'Locale', 'VARCHAR', true, 5, 'en_US');
        $this->addColumn('title', 'Title', 'VARCHAR', true, 255, null);
        $this->getColumn('title', false)->setPrimaryString(true);
        $this->addColumn('content', 'Content', 'LONGVARCHAR', false, null, null);
        $this->addColumn('active', 'Active', 'BOOLEAN', false, 1, true);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Page', 'Etfostra\\ContentBundle\\Model\\Page', RelationMap::MANY_TO_ONE, array('id' => 'id', ), 'CASCADE', null);
    } // buildRelations()

} // PageI18nTableMap
