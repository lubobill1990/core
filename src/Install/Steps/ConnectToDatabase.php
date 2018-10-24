<?php

/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Install\Steps;

use Flarum\Install\Step;
use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Database\MySqlConnection;
use PDO;
use RangeException;

class ConnectToDatabase implements Step
{
    private $dbConfig;
    private $store;

    public function __construct($dbConfig, callable $store)
    {
        $this->dbConfig = $dbConfig;

        $this->store = $store;
    }

    public function getMessage()
    {
        return 'Connecting to database';
    }

    public function run()
    {
        $pdo = (new MySqlConnector)->connect($this->dbConfig);

        $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (version_compare($version, '5.5.0', '<')) {
            throw new RangeException('MySQL version too low. You need at least MySQL 5.5.');
        }

        ($this->store)(
            new MySqlConnection(
                $pdo,
                $this->dbConfig['database'],
                $this->dbConfig['prefix'],
                $this->dbConfig
            )
        );
    }
}