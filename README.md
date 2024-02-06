# wp-time
Time - WordPress Plugin

JavaScript tool for conditionally displaying an element depending on time.

Example:

Show current time
```html
<span data-time="h:MM:ss"></span>
```

Detail documentation for Date/Time format at 
<a href="https://github.com/felixge/node-dateformat">https://github.com/felixge/node-dateformat</a>

Conditional display

```html
<span data-on-time='{"on":"0/10 * * * * *","off":"5/10 * * * * *"}'>Show 0-5s</span>
<span data-on-time='{"on":"0/10 * * * *","off":"5/10 * * * *"}'>Show 0-5m</span>
<span data-on-time='{"on":"5/10 * * * *","off":"10/10 * * * *"}'>Show 5-10m</span>

```

Detail documentation for Cron Expression at <a href="https://github.com/harrisiirak/cron-parser#readme">https://github.com/harrisiirak/cron-parser#readme</a>

Update dependencies
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