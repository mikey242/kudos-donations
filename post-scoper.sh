#!/bin/bash
set -e

echo "🧹 Cleaning up unscoped vendor packages..."

rm -rf vendor/mollie
rm -rf vendor/monolog

echo "✅ Removed vendor copies of Mollie and Monolog"
