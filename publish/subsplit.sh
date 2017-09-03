#!/usr/bin/env bash
cd ..
git subsplit update

git subsplit publish "
    src/Laranix/AntiSpam:git@github.com:laranix/AntiSpam.git
    src/Laranix/Auth:git@github.com:laranix/auth.git
    src/Laranix/Foundation:git@github.com:laranix/Foundation.git
    src/Laranix/Installer:git@github.com:laranix/Installer.git
    src/Laranix/Session:git@github.com:laranix/Session.git
    src/Laranix/Support:git@github.com:laranix/Support.git
    src/Laranix/Themer:git@github.com:laranix/Themer.git
    src/Laranix/Tracker:git@github.com:laranix/Tracker.git
" --heads="master 2.0"
