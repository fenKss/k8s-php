# Helm

Обновление зависимостей чарта

```shell
helm dependency update ./app 
```

Утстановка чарта

```shell
helm install app ./app 
```

Обновление конфигурации

```shell
helm upgrade app ./app --recreate-pods 
```

# Развернуть kiali

```shell
kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.12/samples/addons/prometheus.yaml &&
kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.12/samples/addons/kiali.yaml &&
kubectl label namespace default istio-injection=enabled
```

# Зайти в kiali

```shell
istioctl dashboard kiali 
```

# Kafka

Добавить сообщение в топик кафки(из kafka-client)

```shell
kafka-console-producer --broker-list app-kafka:9092 --property key.separator=, --property parse.key=true --topic
>1,{"__event":"Send", "user_token":"1c4845a2-b4d6-459f-8d43-30d71e84f1e0", "message": "Test message"}
```