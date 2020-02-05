# Contributing guidelines

## Pull Request Checklist

Before sending your pull requests, make sure you followed this list.

- Read [contributing guidelines](CONTRIBUTING.md).
- Read [Code of Conduct](CODE_OF_CONDUCT.md).
- Ensure you have signed your commits.
- Check if changes are consistent with the [guidelines]().
- Check if changes are consistent with the [Coding Style]().
- Run [Unit Tests]().
- Follow the writing guidelines of [pull request template](.github/PULL_REQUEST_TEMPLATE.md).

### Contribution guidelines and standards

Before sending your pull request for [review](https://github.com/adclick/devops.docker.bootstrap),
make sure your changes are consistent with the guidelines, follow the coding
style and the naming convetions.

#### General guidelines and philosophy for contribution

* Include unit tests when you contribute new features, as they help to
  a) prove that your code works correctly, and b) guard against future breaking
  changes to lower the maintenance cost.
* Bug fixes also generally require unit tests, because the presence of bugs
  usually indicates insufficient test coverage.
* When you contribute a new feature, the maintenance burden is (by
  default) transferred to someone else in the team. This means that benefit of the
  contribution must be compared against the cost of maintaining the feature.

#### Branch naming conventions

- Use grouping tokens (words) at the beginning of your branch names.
- Define and use short lead tokens to differentiate branches in a way that is meaningful to your workflow.
- Use slashes to separate parts of your branch names.
- Do not use bare numbers as leading parts.
- Avoid long descriptive names for long-lived branches.
- use Jira's issue number by the end of the name "add-feature-xpto-eai-10"
- Use "grouping" tokens in front of your branch names. You may use:
  - Feature branches (feature/)
  - Bug branches (bug/)
  - Hotfix branches (hotfix/)

#### Git Commit message

- Separate subject from body with a blank line
- Limit the subject line to 50 characters
- Capitalize the subject line
- Do not end the subject line with a period
- Use the imperative mood in the subject line
- Wrap the body at 72 characters
- Use the body to explain what and why vs. how

##### Format of the commit message:
```bash
<type>(<scope>): <subject>

<body>

<footer>
```

###### Example commit message:

```bash
fix(app): Remove data fetching from twig extension

Changes block that now renders itself and fetch the data before the twig

Fixes CMSWP-43
```

###### Message subject (first line)
The first line cannot be longer than 50 characters, the second line is always blank and
other lines should be wrapped at 72 characters. The type and scope should
always be lowercase as shown below.

####### Allowed `<type>` values:

* **feat** (new feature for the user, not a new feature for build script)
* **fix** (bug fix for the user, not a fix to a build script)
* **docs** (changes to the documentation)
* **style** (formatting, missing semi colons, etc; no production code change)
* **refactor** (refactoring production code, eg. renaming a variable)
* **test** (adding missing tests, refactoring tests; no production code change)
* **chore** (updating grunt tasks etc; no production code change)

####### Example `<scope>` values:

* docker
* devops
* app
* config
* task
* etc.

The `<scope>` can be empty (e.g. if the change is a global or difficult
to assign to a single component), in which case the parentheses are
omitted. In smaller projects such as plugins, the `<scope>` is empty.


###### Message body
* uses the imperative, present tense: “change” not “changed” nor “changes”
* includes motivation for the change and contrasts with previous behavior

For more info about message body, see:

* http://365git.tumblr.com/post/3308646748/writing-git-commit-messages
* http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html


###### Message footer

####### Referencing issues
Closed issues should be listed on a separate line in the footer prefixed with "Closes" keyword like this:
```bash
Closes CMSWP-43
```
or in the case of multiple issues:
```bash
Closes CMSWP-43, LB-44, LC-45
```
####### Breaking changes

All breaking changes have to be mentioned in footer with the
description of the change, justification and migration notes.
```bash
BREAKING CHANGE:

`port-runner` command line option has changed to `runner-port`, so that it is
consistent with the configuration file syntax.

To migrate your project, change all the commands, where you use `--port-runner`
to `--runner-port`.
```

---

These rules are based on http://karma-runner.github.io/3.0/dev/git-commit-msg.html

