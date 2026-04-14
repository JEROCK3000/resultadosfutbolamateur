#!/bin/bash
# deploy.sh — Script de despliegue a producción
# Uso: ./deploy.sh "Mensaje del commit"
# Ejemplo: ./deploy.sh "feat: módulo de encuentros completado"

set -e

MSG="${1:-Deploy automático $(date '+%Y-%m-%d %H:%M:%S')}"

echo ""
echo "⚽ ======================================="
echo "   Sistema Multiligas — Deploy"
echo "   $(date '+%Y-%m-%d %H:%M:%S')"
echo "========================================="
echo ""

echo "📦 Preparando commit..."
git add .
git commit -m "$MSG" || echo "  → Sin cambios nuevos para commitear"

echo ""
echo "🚀 Enviando a GitHub (origin main)..."
git push origin main

echo ""
echo "🌐 Desplegando en producción..."
# IMPORTANTE: Reemplazar con los datos reales del servidor de producción
# ssh usuario@tu-servidor.com "cd /var/www/resultadosfutbol && git pull origin main"

echo ""
echo "✅ Deploy completado: $MSG"
echo "==========================================="
