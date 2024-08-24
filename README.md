# 📦 Appwrite Box

Self-hosted Appwrite in single container for local development and automated tests (CI/CD)


```
docker build -t appwrite-box . && docker run -it --rm -v /var/run/docker.sock:/var/run/docker.sock -v ./appwrite.json:/mnt/appwrite.json appwrite-box
```
