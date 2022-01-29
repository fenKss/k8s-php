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