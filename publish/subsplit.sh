#!/usr/bin/env bash
cd ..
git subsplit init git@github.com:samanix/laranix.git
git subsplit publish src/Laranix/AntiSpam:git@github.com:laranix/AntiSpam.git
git subsplit publish src/Laranix/Auth:git@github.com:laranix/auth.git
git subsplit publish src/Laranix/Foundation:git@github.com:laranix/Foundation.git
git subsplit publish src/Laranix/Installer:git@github.com:laranix/Installer.git
git subsplit publish src/Laranix/Session:git@github.com:laranix/Session.git
git subsplit publish src/Laranix/Support:git@github.com:laranix/Support.git
git subsplit publish src/Laranix/Themer:git@github.com:laranix/Themer.git
git subsplit publish src/Laranix/Tracker:git@github.com:laranix/Tracker.git
rm -rf .subsplit/
