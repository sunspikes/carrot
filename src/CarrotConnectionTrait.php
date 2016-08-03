<?php

namespace Sunspikes\Carrot;

trait CarrotConnectionTrait
{
    /**
     * Close the current connection
     */
    protected function closeConnection()
    {
        if ($this->channel) {
            $this->channel->close();
            $connection = $this->channel->getConnection();

            if ($connection) {
                $connection->close();
            }
        }
    }
}