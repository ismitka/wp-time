# wp-time
Time - WordPress Plugin

JavaScript tool for conditionally displaying an element depending on time.

Example:

Show current time
```text
[wp-time format="h:MM:ss"]
```

```html
<span data-time="h:MM:ss"></span>
```

Detail documentation for Date/Time format at 
<a href="https://github.com/felixge/node-dateformat">https://github.com/felixge/node-dateformat</a>

Conditional display

```text
[wp-time-on on="0/10 * * * * *" off="5/10 * * * * *"]
Show on each 0s (0s 10s 20s...) and Hide on each 5s (5s 15s 25s...) 
```

```html
<span data-on-time='{"on":"0/10 * * * * *","off":"5/10 * * * * *"}'>Show 0-5s</span>
<span data-on-time='{"on":"0/10 * * * *","off":"5/10 * * * *"}'>Show 0-5m</span>
<span data-on-time='{"on":"5/10 * * * *","off":"10/10 * * * *"}'>Show 5-10m</span>

```

Detail documentation for Cron Expression at <a href="https://github.com/harrisiirak/cron-parser#readme">https://github.com/harrisiirak/cron-parser#readme</a>

Compose / Test Crom Expression at https://crontab.guru/

### Update dependencies

```bash
npm install
```

Compile JS
```bash
pnpm run build
```

Create plugin archive
```bash
./compress.sh
```