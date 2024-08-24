<?

require_once '../vendor/autoload.php';

declare(ticks = 1);

use Utopia\CLI\Console;
use Utopia\Fetch\Client;

pcntl_signal(SIGINT, function() {
    die;
    exit();
});

$output = '';

echo "Cleaning up previous environment ..." . "\n";

Console::execute('docker rm --force $(docker ps -aq --filter "label=com.docker.compose.project.config_files=/root/appwrite/docker-compose.yml")', '', $output);

Console::execute('cd appwrite && docker compose down -v', '', $output);

echo "Pulling Appwrite images ..." . "\n";

Console::execute("cd appwrite && docker compose pull", '', $output);

echo "Starting new environment ..." . "\n";

Console::execute("cd appwrite && docker compose up -d", '', $output);

echo "Waiting for environment to be ready ... " . "\n";

while(true) {
    $response = Console::execute('curl -s -o /dev/null -w "%{http_code}" http://172.17.0.1:9000/v1/account', '', $output);

    if(\is_string($response) && \trim($response) === "401") {
        break;
    }

    echo "Retrying ..." . "\n";
    \sleep(2);
}

echo "Preparing account ... " . "\n";

$client = new Client();
$client->addHeader('content-type', Client::CONTENT_TYPE_APPLICATION_JSON);

$resp = $client->fetch(
    url: "http://172.17.0.1:9000/v1/account",
    method: 'POST',
    body: [
        'userId' => 'unique()',
        'email' => 'admin@appwrite.box',
        'password' => 'password',
        'name' => 'Appwrite Box'
    ]
);

die();

\shell_exec('curl -w "%{http_code}" -X POST -H "Content-Type: application/json" -s \'http://172.17.0.1:9000/v1/account/sessions/email\' --data-raw \'{"email":"admin@appwrite.box","password":"password"}\'');

echo "Preparing organization ... " . "\n";

\shell_exec('curl -w "%{http_code}" -X POST -H "Content-Type: application/json" -s \'http://172.17.0.1:9000/v1/teams\' --data-raw \'{"teamId":"appwrite-box","name":"Appwrite Box"}\'');

echo "Preparing project ... " . "\n";

$json = \file_get_contents('/mnt/appwrite.json');
$json = \json_decode($json, true);

$projectId = $json['projectId'];
$projectName = $json['projectName'] ?? 'Unnamed';

\shell_exec('curl -w "%{http_code}" -X POST -H "Content-Type: application/json" -s \'http://172.17.0.1:9000/v1/projects\' --data-raw \'{"projectId":"' . $projectId . '","name":"' . $projectName . '","teamId":"appwrite-box","region":"default"}\'');

echo "Pushing configuration ... " . "\n";

\shell_exec('appwrite login --endpoint="http://172.17.0.1:9000/v1" --email="admin@appwrite.box" --password="password"');

\shell_exec('cd /mnt && appwrite push --all --force');

echo "Done. " . "\n";

echo "Endpoint: http://localhost:9000/" . "\n";
echo "Console email: admin@appwrite.box" . "\n";
echo "COnsole password: password" . "\n";