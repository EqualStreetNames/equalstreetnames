# Replicate the EqualStreetNames project for your city

The EqualStreetNames project is built so it's (relatively) easy to replicate in any city in the World !  
The data and file specific to a city are hosted in a separated repository.

## Process

The easiest way is to use the [template](https://github.com/EqualStreetNames/equalstreetnames-template) on GitHub.
You can press the `Use this template` button, type in a name and create a repository that way.

Each city repository is a [sub-module](https://git-scm.com/book/en/v2/Git-Tools-Submodules) of the [main repository](https://github.com/EqualStreetNames/equalstreetnames). A sub-module is working as a sub-folder in the `cities` folder of the main repository.

Afterwards you should be able to follow the instructions in the supplied README.md file to complete the setup.

More documentation about the update scripts can be found in the [scripts documentation](./scripts/README.md).
