#!/usr/bin/env bash
echo "upgrading $BOX_NAME"

echo "Update Package List"
sudo apt-get update

echo "Install Kernel Headers"
sudo apt-get install -y -qq linux-headers-$(uname -r) build-essential software-properties-common apt-transport-https

echo "Upgrade System Packages"
sudo apt-get upgrade -y
