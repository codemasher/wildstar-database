# requires:
# - https://app.vagrantup.com/ubuntu/boxes/bionic64
# - https://github.com/vagrant-landrush/landrush
# - https://github.com/aidanns/vagrant-reload
# - https://github.com/dotless-de/vagrant-vbguest (optional)

# change this to your project's name
VIRTUALBOX_DISPLAY_NAME = 'WildstarDB'

# change to your wildstar installation folder
WILDSTAR_CLIENT_DIR = './WildStar'

Vagrant.configure(2) do |config|
    config.vm.box = 'ubuntu/bionic64'
    config.vm.hostname = 'wildstar.db'
    config.ssh.insert_key = false
    config.ssh.shell = "bash -c 'BASH_ENV=/etc/profile exec bash'"
    config.ssh.forward_agent = true

    config.vm.provider :virtualbox do |vb|
        vb.name = VIRTUALBOX_DISPLAY_NAME

        vb.customize ["modifyvm", :id, "--cpus", "4"]
        vb.customize ['modifyvm', :id, '--memory', '16384']
        vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
        vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
        vb.customize ["modifyvm", :id, "--ostype", "Ubuntu_64"]

        # Display the VirtualBox GUI when booting the machine
        vb.gui = true
        vb.customize ["modifyvm", :id, "--clipboard", "bidirectional"]
    end

    config.vm.synced_folder './', '/vagrant', id: 'vagrant-root', :owner => 'www-data', :group => 'www-data', :mount_options => ["dmode=777","fmode=777"]
    config.vm.synced_folder WILDSTAR_CLIENT_DIR, '/wildstar', id: 'wildstar-root', :owner => 'vagrant', :group => 'vagrant', :mount_options => ["dmode=777","fmode=777"]
    config.vm.network "private_network", ip: "192.168.10.10"
    config.vm.network 'forwarded_port', guest: 80, host: 8000, auto_correct: true
    config.vm.network 'forwarded_port', guest: 443, host: 44300, auto_correct: true
    config.vm.network 'forwarded_port', guest: 1433, host: 14330, auto_correct: true
    config.vm.network 'forwarded_port', guest: 3050, host: 30500, auto_correct: true
    config.vm.network 'forwarded_port', guest: 3306, host: 33060, auto_correct: true
    config.vm.network 'forwarded_port', guest: 5432, host: 54320, auto_correct: true

    config.landrush.enabled = true
    config.landrush.tld = 'db'
    config.landrush.host 'wildstar.db'

    config.vm.provision :shell, inline: 'sudo echo "source /vagrant/scripts/provision-envvars.sh" > /etc/profile.d/env-config.sh', :run => 'always'
    config.vm.provision :shell, inline: 'sudo sudo apt-get update'

    # install openssl before anything and restart to prevent breaking the unattended install
	# https://bugs.launchpad.net/ubuntu/+source/ansible/+bug/1833013/comments/6
    config.vm.provision :shell, inline: 'sudo DEBIAN_FRONTEND=noninteractive UCF_FORCE_CONFOLD=1 apt-get install -y -qq -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" openssl'
    config.vm.provision :reload

    config.vm.provision 'shell', path: './scripts/update.sh'
    config.vm.provision :reload
    config.vm.provision 'shell', path: './scripts/install-amp.sh'
    config.vm.provision :reload
    config.vm.provision :shell, inline: 'sudo systemctl reload-or-restart apache2'
#    config.vm.provision "shell", inline: 'cd /vagrant && composer install --no-interaction --prefer-source'
#    config.vm.provision "shell", inline: 'cd /vagrant && phpunit --configuration phpunit.xml'
end
