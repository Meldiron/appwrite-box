<?

require_once './vendor/autoload.php';

declare(ticks = 1);

use Utopia\CLI\Console;
use Utopia\Fetch\Client;

pcntl_signal(SIGINT, function() {
    die;
    exit();
});

function execute(string $command) {
    $output = '';
    $code = Console::execute($command, '', $output);

    if($code !== 0) {
        var_dump($output);
        die();
    }
}

echo "Cleaning up previous environment ..." . "\n";

execute('docker rm --force $(docker ps -aq --filter "label=com.docker.compose.project.config_files=/root/appwrite/docker-compose.yml") 2> /dev/null || true');

execute('cd appwrite && docker compose down -v');

echo "Pulling Appwrite images ..." . "\n";

execute("cd appwrite && docker compose pull");

echo "Starting new environment ..." . "\n";

execute("cd appwrite && docker compose up -d");

echo "Waiting for environment to be ready ... " . "\n";

$client = new Client();

$endpoint = '';

while(true) {
    try {
        $response = $client->fetch(
            url: "http://172.17.0.1:9000/v1/account",
            method: 'GET'
        );
    
        if($response->getStatusCode() === 401) {
            $endpoint = 'http://172.17.0.1:9000';
            break;
        }
    } catch(Throwable $err) {}

    try {
        $response = $client->fetch(
            url: "http://host.docker.internal:9000/v1/account",
            method: 'GET'
        );
    
        if($response->getStatusCode() === 401) {
            $endpoint = 'http://host.docker.internal:9000';
            break;
        }
    } catch(Throwable $err) {}

    echo "Retrying ..." . "\n";
    \sleep(2);
}

echo "Preparing account ... " . "\n";

$client->addHeader('content-type', Client::CONTENT_TYPE_APPLICATION_JSON);

$response = $client->fetch(
    url: $endpoint . "/v1/account",
    method: 'POST',
    body: [
        'userId' => 'unique()',
        'email' => 'admin@appwrite.box',
        'password' => 'password',
        'name' => 'Appwrite Box'
    ]
);

if($response->getStatusCode() >= 400) {
    \var_dump($response->getBody());
    die();
}

$response = $client->fetch(
    url: $endpoint . "/v1/account/sessions/email",
    method: 'POST',
    body: [
        'email' => 'admin@appwrite.box',
        'password' => 'password'
    ]
);

if($response->getStatusCode() >= 400) {
    \var_dump($response->getBody());
    die();
}

$cookie = $response->getHeaders()['set-cookie'];
$client->addHeader('cookie', $cookie);

echo "Preparing organization ... " . "\n";

$response = $client->fetch(
    url: $endpoint . "/v1/teams",
    method: 'POST',
    body: [
        'teamId' => 'appwrite-box',
        'name' => 'Appwrite Box'
    ]
);

if($response->getStatusCode() >= 400) {
    \var_dump($response->getBody());
    die();
}

echo "Preparing project ... " . "\n";

$json = \file_get_contents('/mnt/appwrite.json');
$json = \json_decode($json, true);
$projectId = $json['projectId'];
$projectName = $json['projectName'] ?? 'Unnamed';

$response = $client->fetch(
    url: $endpoint . "/v1/projects",
    method: 'POST',
    body: [
        'teamId' => 'appwrite-box',
        'region' => 'default',
        'projectId' => $projectId,
        'name' => $projectName,
    ]
);

if($response->getStatusCode() >= 400) {
    \var_dump($response->getBody());
    die();
}

echo "Pushing configuration ... " . "\n";


execute('appwrite login --endpoint="' . $endpoint . '/v1" --email="admin@appwrite.box" --password="password"');

execute('cd /mnt && appwrite push --all --force');

echo "Done. " . "\n";

echo "Endpoint: http://localhost:9000/" . "\n";
echo "Console email: admin@appwrite.box" . "\n";
echo "COnsole password: password" . "\n";