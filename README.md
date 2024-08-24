# 📦 Appwrite Box

Self-hosted Appwrite in single container for local development and automated tests (CI/CD)

## ⚙️ Requirements

- Docker installed

## 📖 Usage

Run following command inside folder with your `appwrite.json`:

```
docker run -it --rm \
    -v /var/run/docker.sock:/var/run/docker.sock \
    -v ./appwrite.json:/mnt/appwrite.json \
    meldiron/appwrite-box:0.1.3
```
