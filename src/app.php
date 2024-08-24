<?

declare(ticks = 1);
pcntl_signal(SIGINT, function() {
    die;
    exit();
});

echo "Cleaning up previous environment ..." . "\n";

\shell_exec('docker rm --force $(docker ps -aq --filter "label=com.docker.compose.project.config_files=/root/appwrite/docker-compose.yml")');
\shell_exec('cd appwrite && docker compose down -v');

echo "Pulling Appwrite images ..." . "\n";

\shell_exec("cd appwrite && docker compose pull");

echo "Starting new environment ..." . "\n";

\shell_exec("cd appwrite && docker compose up -d");

echo "Waiting for environment to be ready ... " . "\n";

while(true) {
    $response = \shell_exec('curl -s -o /dev/null -w "%{http_code}" http://172.17.0.1:9000/v1/account');

    if(\is_string($response) && \trim($response) === "401") {
        break;
    }

    echo "Retrying ..." . "\n";
    \sleep(2);
}

echo "Preparing account ... " . "\n";

\shell_exec('curl -s \'http://172.17.0.1:9000/v1/account\' --data-raw \'{"userId":"unique()","email":"admin@appwrite.box","password":"password","name":"Appwrite Box"}\'');

\shell_exec('curl -s \'http://172.17.0.1:9000/v1/account/sessions/email\' --data-raw \'{"email":"admin@appwrite.box","password":"password"}\'');

echo "Preparing organization ... " . "\n";

\shell_exec('curl -s \'http://172.17.0.1:9000/v1/teams\' --data-raw \'{"teamId":"appwrite-box","name":"Appwrite Box"}\'');

echo "Preparing project ... " . "\n";

$json = \file_get_contents('/mnt/appwrite.json');
$json = \json_decode($json, true);

$projectId = $json['projectId'];
$projectName = $json['projectName'] ?? 'Unnamed';

\shell_exec('curl -s \'http://172.17.0.1:9000/v1/projects\' --data-raw \'{"projectId":"' . $projectId . '","name":"' . $projectName . '","teamId":"appwrite-box","region":"default"}\'');

echo "Pushing configuration ... " . "\n";

\shell_exec('appwrite login --endpoint="http://172.17.0.1:9000/v1" --email="admin@appwrite.box" --password="password"');

\shell_exec('cd /mnt && appwrite push --all --force');

echo "Done. " . "\n";

echo "Endpoint: http://localhost:9000/" . "\n";
echo "Console email: admin@appwrite.box" . "\n";
echo "COnsole password: password" . "\n";