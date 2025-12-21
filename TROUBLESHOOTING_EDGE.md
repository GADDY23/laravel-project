# Troubleshooting: Application Not Opening in Microsoft Edge

## Quick Fixes

### 1. **Check the URL**
Make sure you're accessing:
```
http://localhost:8000
```
or
```
http://127.0.0.1:8000
```

### 2. **Clear Browser Cache**
- Press `Ctrl + Shift + Delete`
- Select "Cached images and files"
- Click "Clear now"
- Try accessing the site again

### 3. **Check if Vite Dev Server is Running**
Open a new terminal and run:
```powershell
cd C:\laragon\www\SCHOOL-SCHEDULING
npm run dev
```

You should see:
```
VITE v7.x.x  ready in xxx ms
➜  Local:   http://localhost:5173/
```

### 4. **Check Laravel Server**
Make sure PHP server is running:
```powershell
cd C:\laragon\www\SCHOOL-SCHEDULING
php artisan serve
```

You should see:
```
INFO  Server running on [http://127.0.0.1:8000]
```

### 5. **Check Browser Console for Errors**
1. Open Microsoft Edge
2. Press `F12` to open Developer Tools
3. Go to the "Console" tab
4. Look for any red error messages
5. Check the "Network" tab to see if files are loading

### 6. **Try Different URL Formats**
Try these URLs in Edge:
- `http://localhost:8000`
- `http://127.0.0.1:8000`
- `http://0.0.0.0:8000` (if configured)

### 7. **Disable Edge Extensions**
Some extensions can block localhost:
1. Go to `edge://extensions/`
2. Disable all extensions temporarily
3. Try accessing the site again

### 8. **Check Windows Firewall**
1. Open Windows Security
2. Go to Firewall & network protection
3. Make sure Laravel/Vite ports (8000, 5173) are allowed

### 9. **Use InPrivate/Incognito Mode**
Press `Ctrl + Shift + N` in Edge to open InPrivate window and test

### 10. **Check if Ports are in Use**
```powershell
netstat -ano | findstr :8000
netstat -ano | findstr :5173
```

## Common Error Messages

### "This site can't be reached"
- **Solution**: Make sure `php artisan serve` is running

### "Failed to load resource" (Vite assets)
- **Solution**: Make sure `npm run dev` is running

### "CORS error"
- **Solution**: The Vite config has been updated to handle this

### Blank white page
- **Solution**: Check browser console (F12) for JavaScript errors

## If Still Not Working

1. **Restart Both Servers**:
   ```powershell
   # Stop all processes (Ctrl+C in terminals)
   # Then restart:
   php artisan serve
   # In another terminal:
   npm run dev
   ```

2. **Check .env file**:
   Make sure `APP_URL=http://localhost:8000` is set correctly

3. **Try a different browser first** (Chrome/Firefox) to confirm it's Edge-specific

4. **Update Edge browser** to the latest version

5. **Check Edge Settings**:
   - Go to Settings → Privacy, search, and services
   - Make sure "Block potentially unwanted apps" is not blocking localhost
















