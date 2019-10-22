#!/usr/bin/env bash
echo "upgrading $BOX_NAME"

# Update Package List
sudo apt-get update

# Install Kernel Headers
sudo apt-get install -y -qq linux-headers-$(uname -r) build-essential software-properties-common

# Upgrade System Packages
sudo apt-get upgrade -y
