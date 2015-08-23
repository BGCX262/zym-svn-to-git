if (isset($_ENV['PWD'])) {
    chdir($_ENV['PWD']);
}

require_once 'Zend/Loader.php';
require_once 'Zend/Tool/Framework/Client/Cli.php';

// Run the CLI Client
Zend_Tool_Framework_Client_Cli::main();
