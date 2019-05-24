<?php

class DataCopier
{
    /**
     * @var BaseDataProvider|IDataSource $_source
     */
    private $_source;

    /**
     * @var BaseDataProvider|IDataDestination $_destination
     */
    private $_destination;

    /**
     * DatabaseCopier constructor.
     *
     * @param BaseDataProvider|IDataSource $source
     * @param BaseDataProvider|IDataDestination $destination
     */
    public function __construct($source, $destination)
    {
        $this->_source = $source;
        $this->_destination = $destination;
    }

    /**
     * Copies single table from the source to the destination
     *
     * @param string $name - The source table name
     * @param string $dest_name - The name of the destination table
     */
    public function CopyTable($name, $dest_name = null) {
        $table = $this->_source->GetTable($name);

        if (!empty($dest_name)) {
            $table->SetName($dest_name);
        }

        $this->_destination->CreateTable( $table );
    }
}