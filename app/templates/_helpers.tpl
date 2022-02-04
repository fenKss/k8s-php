{{/*
Expand the name of the chart.
*/}}
{{- define "app.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- define "app.secret" -}}
{{ include "app.fullname" . }}-secret
{{- end }}
{{- define "app.kafka" -}}
{{ include "app.fullname" . }}-kafka
{{- end }}
{{- define "app.db" -}}
{{ include "app.fullname" . }}-db
{{- end }}
{{- define "app.billing" -}}
{{ include "app.fullname" . }}-billing
{{- end }}
{{- define "app.notification" -}}
{{ include "app.fullname" . }}-notification
{{- end }}
{{- define "app.order" -}}
{{ include "app.fullname" . }}-order
{{- end }}
{{- define "app.auth" -}}
{{ include "app.fullname" . }}-auth
{{- end }}

{{- define "db.url"}}{{- printf "mysql://%s:%s@%s:%s/%s?appVersion=5.7" "root" .Values.mysql.auth.rootPassword .Values.mysql.fullnameOverride (.Values.mysql.primary.service.port|toString) "%s" }}{{- end }}
{{/*{{- define "db.url"}}*/}}
{{/*{{- printf "mysql://%s:%s@%s:%s/%s?appVersion=5.7" .Values.mysql.auth.username .Values.mysql.auth.password .Values.mysql.fullnameOverride (.Values.mysql.primary.service.port|toString) "%s" }}*/}}
{{/*{{- end }}*/}}
{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "app.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "app.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "app.labels" -}}
app: {{ include "app.fullname" . }}
version: {{ .Chart.Version }}
helm.sh/chart: {{ include "app.chart" . }}
{{ include "app.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "app.selectorLabels" -}}
app.kubernetes.io/name: {{ include "app.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "app.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "app.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}
