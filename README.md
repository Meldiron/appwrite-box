# appwrite-box
Self-hosted Appwrite in single container for local development and automated tests (CI/CD)


```
docker rm --force $(docker ps -aq) && docker run --privileged -p 9000:80 -d -v ./appwrite-box.sh:/mnt/appwrite-box.sh --name appwrite-box docker:dind && docker exec -it appwrite-box /bin/sh -c "chmod +x /mnt/appwrite-box.sh && /mnt/appwrite-box.sh"
```