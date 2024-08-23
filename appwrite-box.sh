set -e
cd /root

echo "--- Starting Docker ..."

sleep 5

echo "--- Installing dependencies ..."

apk update
apk add curl

echo "--- Dowloading Appwrite configs ..."

curl -o docker-compose.yml https://raw.githubusercontent.com/appwrite/appwrite/1.6.x/docker-compose.yml
curl -o .env https://raw.githubusercontent.com/appwrite/appwrite/1.6.x/.env

sed -i 's/appwrite-dev/appwrite\/appwrite:1.6.0-RC9/g' docker-compose.yml

echo "--- Pulling Docker images ..."

docker compose pull

echo "--- Starting Appwrite ..."

docker compose up -d

echo "--- Waiting for bootup ..."

while true
do
    STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:80/versions)

    echo $STATUS_CODE

    if [ $STATUS_CODE -ne 200 ]; then
        sleep 2
        echo "--- Retrying ..."
    else
        break
    fi
done
