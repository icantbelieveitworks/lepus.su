package main

import "io"
import "os"
import "log"
import "fmt"
import "net"
import "time"
import "regexp"
import "strconv"
import "strings"
import "os/user"
import "os/exec"
import "net/http"
import "io/ioutil"
import "encoding/hex"
import "compress/gzip"
import "crypto/sha256"
import "encoding/json"
import "encoding/base64"
import "github.com/gorilla/context"
import "github.com/gorilla/sessions"
import "github.com/kless/osutil/user/crypt/sha512_crypt"

var sess []string
var lepusConf Config
var store = sessions.NewFilesystemStore("sess", []byte(mainConf()))

type Config struct {
	IP    string
	Log   string
	Pages string
	Sess  string `json:"sessKey"`
	Cache string `json:"cache"`
	Port  string `json:"port"`
}

type lepusMes struct {
	Err string
	Mes string
}

type gzipResponseWriter struct {
	io.Writer
	http.ResponseWriter
}

func main() {
	go lepusCleaner()

	mux := http.NewServeMux()
	mux.HandleFunc("/", Gzip(lepusPage))
	mux.HandleFunc("/favicon.ico", Gzip(lepusFavicon))
	mux.HandleFunc("/api/login", Gzip(lepusLoginAPI))
	mux.HandleFunc("/api/exit", Gzip(lepusExitAPI))
	mux.HandleFunc("/api/get", Gzip(lepusGetAPI))
	mux.HandleFunc("/api/test", Gzip(lepusTestAPI))
	mux.HandleFunc("/api/addwebdir", Gzip(lepusAddWebDirAPI))
	mux.HandleFunc("/api/delwebdir", Gzip(lepusDelWebDirAPI))
	mux.HandleFunc("/api/chwebdir", Gzip(lepusChWebDirAPI))
	mux.HandleFunc("/api/weblink", Gzip(lepusAddWebLinkAPI))
	mux.HandleFunc("/api/chwebmode", Gzip(lepusChWebModeAPI))
	mux.HandleFunc("/api/cron", Gzip(lepusCronAPI))
	mux.HandleFunc("/api/dns", Gzip(lepusDNSAPI))
	mux.HandleFunc("/api/dnsrecords", Gzip(lepusRecordsDNSAPI))

	log.Println("Start server on port " + lepusConf.Port)

	// https://github.com/gorilla/sessions
	// If you aren't using gorilla/mux, you need to wrap your handlers with context.ClearHandler as or else you will leak memory!
	log.Fatal(http.ListenAndServeTLS(lepusConf.Port, "./ssl/server.crt", "./ssl/server.key", context.ClearHandler(mux)))
}

func mainConf() string {
	a, config := lepusReadTextFile("./main.conf")
	if !a {
		fmt.Println("main.conf error: " + config)
		os.Exit(1)
	}
	if err := json.Unmarshal([]byte(config), &lepusConf); err != nil {
		fmt.Printf("main.conf error: %s\n", err)
		os.Exit(1)
	}
	lepusConf.IP = lepusGetIP()
	lepusConf.Pages = "./files"
	lepusConf.Log = "./logs/lepuscp.log"
	fmt.Println(lepusConf)
	return lepusConf.Sess
}

func (w gzipResponseWriter) Write(b []byte) (int, error) {
	return w.Writer.Write(b)
}

func Gzip(fn http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "text/plain")
		if !strings.Contains(r.Header.Get("Accept-Encoding"), "gzip") {
			fn(w, r)
			return
		}
		w.Header().Set("Content-Encoding", "gzip")
		gz, _ := gzip.NewWriterLevel(w, gzip.BestSpeed)
		defer gz.Close()
		gzr := gzipResponseWriter{Writer: gz, ResponseWriter: w}
		fn(gzr, r)
	}
}

func lepusCleaner() {
	for {
		if len(sess) > 0 {
			for _, val := range sess {
				path := "/root/lepuscp/sess/session_" + val
				i := lepusPathInfo(path)
				if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
					os.RemoveAll(path)
				}
				if len(sess) > 0 {
					sess = sess[:len(sess)-1]
				}
			}
		}
		time.Sleep(1500 * time.Millisecond)
	}
}

func lepusFavicon(w http.ResponseWriter, r *http.Request) {
	file, _ := ioutil.ReadFile(lepusConf.Pages + "/favicon.ico")
	w.Write(file)
}

func lepusPage(w http.ResponseWriter, r *http.Request) {
	ret := r.URL.Query()
	val := strings.Join(ret["page"], "")
	page := lepusConf.Pages + "/index.html"
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	switch val {
	case "js":
		page = lepusConf.Pages + "/lepus.js"
		w.Header().Set("Content-Type", "text/javascript; charset=utf-8")
	case "css":
		page = lepusConf.Pages + "/style.css"
		w.Header().Set("Content-Type", "text/css; charset=utf-8")
	case "cp":
		page = lepusConf.Pages + "/cp.html"
	case "wwwedit":
		page = lepusConf.Pages + "/wwwedit.html"
	case "cron":
		page = lepusConf.Pages + "/cron.html"
	case "dns":
		page = lepusConf.Pages + "/dns.html"
	case "editdns":
		page = lepusConf.Pages + "/editdns.html"
	}
	if lepusConf.Cache == "yes" {
		if val == "css" || val == "js" {
			w.Header().Set("Cache-Control", "public, max-age=604800")
			w.Header().Set("Last-Modified", lepusLastModified(page))
			w.Header().Set("Expires", time.Now().AddDate(0, 0, 7).Format(http.TimeFormat))
		}
	}
	if lepusAuth(w, r) && page == lepusConf.Pages+"/index.html" {
		http.Redirect(w, r, "https://"+lepusConf.IP+lepusConf.Port+"/?page=cp", 301)
		return
	} else if !lepusAuth(w, r) && ret["page"] != nil && val != "js" {
		http.Redirect(w, r, "https://"+lepusConf.IP+lepusConf.Port, 301)
		return
	}
	file, _ := ioutil.ReadFile(page)
	w.Write(file)
}

func lepusLoginAPI(w http.ResponseWriter, r *http.Request) {
	ip := strings.Split(r.RemoteAddr, ":")[0]
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["login"], "no", 32)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["passwd"], "no", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	i := lepusLogin(strings.Join(r.Form["login"], ""), strings.Join(r.Form["passwd"], ""))
	if i[0] != "right" {
		lepusLog("auth error from ip " + ip)
		w.Write(lepusMessage("Err", "Wrong login or passwd"))
		return
	}
	session, _ := store.Get(r, "lepuscp")
	session.Values["user"] = strings.Join(r.Form["login"], "")
	session.Values["hash"] = lepusSHA256(ip + i[1])
	session.Save(r, w)
	lepusLog("auth sucsess from ip " + ip + " user " + session.Values["user"].(string))
	fmt.Println("sess: " + session.ID + "\nhash: " + session.Values["hash"].(string) + "\nip: " + ip)
	w.Write(lepusMessage("OK", "Sucsess login"))
}

func lepusExitAPI(w http.ResponseWriter, r *http.Request) {
	session, _ := store.Get(r, "lepuscp")
	session.Values["user"] = nil
	session.Values["hash"] = nil
	session.Save(r, w)
	sess = append(sess, session.ID)
}

func lepusLogin(user, passwd string) []string {
	if user != "lepus" {
		fmt.Println("Only lepus can login (for this version)")
		return []string{"wrong", "no_hash"}
	}
	x := lepusFindUser(user)
	if len(x) != 4 {
		fmt.Println("No hash passwd from /etc/shadow")
		return []string{"wrong", "no_hash"}
	}
	salt := "$" + x[1] + "$" + x[2] + "$"
	hash := salt + x[3]
	c := sha512_crypt.New()
	new_hash, _ := c.Generate([]byte(passwd), []byte(salt))
	if hash != new_hash {
		return []string{"wrong", hash}
	} else {
		return []string{"right", hash}
	}
}

func lepusFindUser(user string) []string {
	var userdata []string
	a, str := lepusReadTextFile("/etc/shadow")
	if a {
		result := strings.Split(str, "\n")
		for key := range result {
			x := strings.Split(result[key], ":")
			if x[0] == user {
				userdata = strings.Split(x[1], "$")
				break
			}
		}
	}
	return userdata
}

func lepusAuth(w http.ResponseWriter, r *http.Request) bool {
	status := false
	cookie, _ := r.Cookie("lepuscp")
	if cookie != nil {
		session, _ := store.Get(r, "lepuscp")
		if session.Values["user"] != nil && session.Values["hash"] != nil {
			x := lepusLogin(session.Values["user"].(string), "no")
			if session.Values["hash"].(string) == lepusSHA256(strings.Split(r.RemoteAddr, ":")[0]+x[1]) {
				status = true
			}
		}
		if !status {
			lepusExitAPI(w, r)
		}
	}
	return status
}

func lepusSHA256(val string) string {
	h := sha256.New()
	h.Write([]byte(val))
	return hex.EncodeToString(h.Sum(nil))
}

func lepusMessage(Err, Mes string) []byte {
	m := lepusMes{Err, Mes}
	b, _ := json.Marshal(m)
	return b
}

func lepusLog(val string) {
	t := time.Now()
	_, str := lepusReadTextFile(lepusConf.Log)
	str += "\n" + t.Format("[2006-01-02 15:04:05]") + " " + val
	lepusWriteTextFile(lepusConf.Log, str, 0644)
}

func lepusGetAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	switch val {
	case "login":
		session, _ := store.Get(r, "lepuscp")
		w.Write(lepusMessage("OK", session.Values["user"].(string)))
		return

	case "type":
		a, mes = lepusCheckPost(r.Form["site"], "", 255)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		site := strings.Join(r.Form["site"], "")
		w.Write(lepusMessage("OK", lepusGetTypeWWW(site)))
		return

	case "cron":
		session, _ := store.Get(r, "lepuscp")
		user := session.Values["user"].(string)
		a, mes = lepusReadTextFile("/etc/cron.d/" + user)
		if !a {
			w.Write(lepusMessage("Err", mes))
		} else {
			w.Write(lepusMessage("OK", mes))
		}
		return

	case "dns":
		result := ""
		files, _ := ioutil.ReadDir("/etc/bind/zone")
		for _, f := range files {
			i := lepusPathInfo("/etc/bind/zone/" + f.Name())
			if i["IsNotExist"] == 1 || i["isDir"] == 1 || i["Readlink"] == 1 {
				continue
			}
			result += f.Name() + " "
		}
		w.Write(lepusMessage("OK", result))
		return

	case "dnsrecords":
		a, mes = lepusCheckPost(r.Form["domain"], "", 255)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		domain := strings.Join(r.Form["domain"], "")
		a, str := lepusReadTextFile("/etc/bind/domain/" + domain)
		if !a {
			w.Write(lepusMessage("Err", str))
			return
		}
		w.Write(lepusMessage("OK", str))
		return

	case "www":
		ip := lepusGetIP()
		x := make(map[string]interface{})
		item := make(map[string]string)
		files, _ := ioutil.ReadDir("/var/www/public")
		for _, f := range files {
			key := f.Name()
			i := lepusPathInfo("/var/www/public/" + f.Name())
			if i["isDir"] == 0 || i["IsNotExist"] == 1 {
				continue
			}
			vh := lepusGetTypeWWW(f.Name())
			if r.Form["symlink"] == nil && i["Readlink"] == 1 {
				continue
			}
			if r.Form["symlink"] != nil {
				switch vh {
				case "mod_alias":
					path := "/var/www/public/" + strings.Join(r.Form["symlink"], "")
					real, _ := os.Readlink("/var/www/public/" + f.Name())
					if real != path {
						continue
					}

				case "vhost":
					if strings.Join(r.Form["symlink"], "") != f.Name() {
						continue
					}
					_, str := lepusReadTextFile("/etc/apache2/sites-enabled/" + f.Name() + ".conf")
					result := strings.Split(str, "\n")
					for k := range result {
						o := strings.Split(result[k], " ")
						if o[0] == "ServerAlias" {
							key = strings.Join(o, " ")
						}
					}
				}
			}
			item["ip"] = ip
			item["http"] = vh
			if i["Perm"] != 000 {
				item["status"] = "online"
			} else {
				item["status"] = "disable"
			}
			x[key] = item
			item = make(map[string]string)
		}
		z, _ := json.Marshal(x)
		w.Write(lepusMessage("OK", string(z)))
		return
	}
}

func lepusAddWebLinkAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["link"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["command"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	link := strings.Join(r.Form["link"], "")
	command := strings.Join(r.Form["command"], "")
	mode := lepusGetTypeWWW(val)
	switch mode {
	case "mod_alias":
		pathSite := "/var/www/public/" + val
		pathLink := "/var/www/public/" + link
		if command == "add" {
			i := lepusPathInfo(pathSite)
			if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
				w.Write(lepusMessage("Err", "Site is not exist"))
				return
			}
			i = lepusPathInfo(pathLink)
			if i["IsNotExist"] == 0 {
				w.Write(lepusMessage("Err", "Link already exist"))
				return
			}
			session, _ := store.Get(r, "lepuscp")
			a, _ := user.Lookup(session.Values["user"].(string))
			os.Symlink(pathSite, pathLink)
			exec.Command("chown", "-h", a.Uid+":"+a.Gid, pathLink).Output()
		}
		if command == "del" {
			i := lepusPathInfo(pathSite)
			if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
				w.Write(lepusMessage("Err", "Main dir not found"))
				return
			}
			i = lepusPathInfo(pathLink)
			if i["Readlink"] == 0 || i["isDir"] == 0 || i["IsNotExist"] == 1 {
				w.Write(lepusMessage("Err", "Link dir not found"))
				return
			}
			pathReal, _ := os.Readlink(pathLink)
			if pathReal != pathSite {
				w.Write(lepusMessage("Err", "Link != Dir"))
				return
			}
			fmt.Println(pathLink + " => " + pathReal)
			os.RemoveAll(pathLink)
		}
		w.Write(lepusMessage("OK", "Done"))
	case "vhost":
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		// lepusGetTypeWWW already check lepusPathInfo
		if command == "add" {
			w.Write(lepusApacheAlias("add", link, confPath))
		} else {
			w.Write(lepusApacheAlias("del", link, confPath))
		}
	}
}

func lepusAddWebDirAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["mode"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	mode := strings.Join(r.Form["mode"], "")
	if mode == "vhost" {
		a, config := lepusReadTextFile("./files/tmpl/apache.tmpl")
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		str := lepusReplaceText(config, "%domain%", val)
		path := "/etc/apache2/sites-enabled/" + val + ".conf"
		a, mes = lepusWriteTextFile(path, str, 0755)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		lepusExecInit("/etc/init.d/apache2", "reload")
	}
	path := "/var/www/public/" + val
	i := lepusPathInfo(path)
	if i["IsNotExist"] == 0 {
		w.Write(lepusMessage("Err", "Dir exist"))
		return
	}
	session, _ := store.Get(r, "lepuscp")
	u, _ := user.Lookup(session.Values["user"].(string))
	uid, _ := strconv.Atoi(u.Uid)
	gid, _ := strconv.Atoi(u.Gid)
	os.Mkdir(path, 0755)
	os.Chown(path, uid, gid)
	w.Write(lepusMessage("OK", lepusGetIP()))
}

func lepusDelWebDirAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	mode := lepusGetTypeWWW(val)
	if mode == "vhost" {
		// no need check lepusPathInfo => already check it (in lepusGetTypeWWW) => conf file exist, not dir and not link.
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		os.RemoveAll(confPath)
		leConfPath := "/etc/apache2/sites-enabled/" + val + "-le-ssl.conf"
		i := lepusPathInfo(leConfPath)
		if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
			os.RemoveAll(leConfPath)
		}
		lepusExecInit("/etc/init.d/apache2", "reload")
	}
	pathSite := "/var/www/public/" + val
	i := lepusPathInfo(pathSite)
	if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
		w.Write(lepusMessage("Err", "Not found"))
		return
	}
	files, _ := ioutil.ReadDir("/var/www/public")
	for _, f := range files {
		i = lepusPathInfo("/var/www/public/" + f.Name())
		if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 0 || lepusRegexp(f.Name(), "") == false {
			continue
		}
		pathReal, _ := os.Readlink("/var/www/public/" + f.Name())
		if pathReal == pathSite {
			fmt.Println("/var/www/public/" + f.Name() + " => " + pathReal)
			os.RemoveAll("/var/www/public/" + f.Name())
		}
	}
	os.RemoveAll(pathSite)
	w.Write(lepusMessage("OK", "Done"))
}

func lepusApacheAlias(command, alias, confPath string) []byte {
	val, val2, new := "", "", ""
	a, str := lepusReadTextFile(confPath)
	if !a {
		return lepusMessage("Err", str)
	}
	result := strings.Split(str, "\n")
	for key := range result {
		o := strings.Split(result[key], " ")
		if o[0] == "ServerAlias" {
			val = strings.Join(o, " ")
			if stringInSlice(alias, o) && command == "add" {
				return lepusMessage("Err", "Domain already add")
			}
		}
		if o[0] == "ServerName" {
			val2 = strings.Join(o, " ")
		}
	}
	if command == "add" && val != "" {
		new = val + " " + alias
		regex, _ := regexp.Compile("\\s+")
		new = regex.ReplaceAllString(new, " ")
	}
	if command == "add" && val == "" {
		val = val2
		new = val + "\nServerAlias " + alias
	}
	if command == "del" {
		new = strings.Replace(val, alias, "", -1)
		regex, _ := regexp.Compile("\\s+")
		new = regex.ReplaceAllString(new, " ")
		if strings.Trim(new, " ") == "ServerAlias" {
			new = ""
		}
	}
	if val == "" {
		return lepusMessage("Err", "Nothing to do")
	}
	str = lepusReplaceText(str, val, new)
	a, mes := lepusWriteTextFile(confPath, str, 0755)
	if !a {
		return lepusMessage("Err", mes)
	}
	lepusExecInit("/etc/init.d/apache2", "reload")
	return lepusMessage("OK", "Done")
}

func lepusGetIP() string {
	x := "0.0.0.0"
	addrs, _ := net.InterfaceAddrs()
	for _, a := range addrs {
		if ipnet, ok := a.(*net.IPNet); ok && !ipnet.IP.IsLoopback() {
			if ipnet.IP.To4() != nil {
				x = ipnet.IP.String()
				break
			}
		}
	}
	return x
}

func lepusChWebDirAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	path := "/var/www/public/" + val
	i := lepusPathInfo(path)
	if i["IsNotExist"] == 1 {
		w.Write(lepusMessage("Err", "Not found"))
		return
	}
	if i["Readlink"] == 1 {
		w.Write(lepusMessage("Err", "It`s symlink"))
		return
	}
	if i["isDir"] != 1 {
		w.Write(lepusMessage("Err", "It isn`t dir"))
		return
	}
	if i["Perm"] == 000 {
		os.Chmod(path, 0755)
		w.Write(lepusMessage("OK", "online"))
	} else {
		os.Chmod(path, 0000)
		w.Write(lepusMessage("OK", "disable"))
	}
}

func lepusRegexp(data, val string) bool {
	re := regexp.MustCompile("^[a-z0-9._-]*$")
	switch val {
	case "cronTime":
		re = regexp.MustCompile("^[0-9/,* ]*$")
	case "cronCommand":
		re = regexp.MustCompile("^[0-9a-zA-Z.=_&?:/-]*$")
	case "09":
		re = regexp.MustCompile("^[0-9]*$")
	case "az":
		re = regexp.MustCompile("^[a-z]*$")
	case "base64":
		re = regexp.MustCompile("^[\\s\\t\\wA-Za-z0-9!\"#$%&'()*+,\\-./:;<=>?@^_`{|}~]*$")
	}
	return re.MatchString(data)
}

func lepusPathInfo(val string) map[string]int {
	info := make(map[string]int)
	info["IsNotExist"] = 0
	info["isDir"] = 0
	info["Readlink"] = 0
	dir, err := os.Stat(val)
	if os.IsNotExist(err) {
		info["IsNotExist"] = 1
	} else {
		if dir.IsDir() {
			info["isDir"] = 1
			mode := dir.Mode()
			j := mode.String()
			x := fmt.Sprintf("%d%d%d", lepusPermToInt(j[1:4]), lepusPermToInt(j[4:7]), lepusPermToInt(j[7:10]))
			info["Perm"], _ = strconv.Atoi(x)
		}
		_, err = os.Readlink(val)
		if err == nil {
			info["Readlink"] = 1
		}
	}
	return info
}

func lepusPermToInt(val string) int {
	x := 0
	switch val {
	case "rwx":
		x = 7
	case "rw-":
		x = 6
	case "r-x":
		x = 5
	case "r--":
		x = 4
	case "-wx":
		x = 3
	case "-w-":
		x = 2
	case "--x":
		x = 1
	}
	return x
}

func lepusGetTypeWWW(val string) string {
	i := lepusPathInfo("/etc/apache2/sites-enabled/" + val + ".conf")
	if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
		return "vhost"
	}
	return "mod_alias"
}

func stringInSlice(a string, list []string) bool {
	for _, b := range list {
		if b == a {
			return true
		}
	}
	return false
}

func lepusChWebModeAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["mode"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	mode := strings.Join(r.Form["mode"], "")
	if mode == lepusGetTypeWWW(val) {
		w.Write(lepusMessage("Err", "Same mode"))
		return
	}
	pathSite := "/var/www/public/" + val
	i := lepusPathInfo(pathSite)
	if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 1 {
		w.Write(lepusMessage("Err", "Not found"))
		return
	}
	switch mode {
	case "mod_alias":
		str := ""
		session, _ := store.Get(r, "lepuscp")
		a, _ := user.Lookup(session.Values["user"].(string))
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		_, file := lepusReadTextFile(confPath)
		result := strings.Split(file, "\n")
		for key := range result {
			o := strings.Split(result[key], " ")
			if o[0] == "ServerAlias" {
				str = strings.Join(o, " ")
			}
		}
		os.RemoveAll(confPath)
		if str != "" {
			regex, _ := regexp.Compile("\\s+")
			str = regex.ReplaceAllString(str, " ")
			result := strings.Split(str, " ")
			for key := range result {
				if result[key] == "" || result[key] == "ServerAlias" || lepusRegexp(result[key], "") == false {
					continue
				}
				pathLink := "/var/www/public/" + result[key]
				i := lepusPathInfo(pathLink)
				if i["IsNotExist"] == 0 {
					continue
				}
				os.Symlink(pathSite, pathLink)
				exec.Command("chown", "-h", a.Uid+":"+a.Gid, pathLink).Output()
			}
		}
		leConfPath := "/etc/apache2/sites-enabled/" + val + "-le-ssl.conf"
		i := lepusPathInfo(leConfPath)
		if i["IsNotExist"] == 0 && i["isDir"] == 0 && i["Readlink"] == 0 {
			os.RemoveAll(leConfPath)
		}

	case "vhost":
		a, config := lepusReadTextFile("./files/tmpl/apache.tmpl")
		if !a {
			w.Write(lepusMessage("Err", config))
			return
		}
		result := lepusReplaceText(config, "%domain%", val)
		confPath := "/etc/apache2/sites-enabled/" + val + ".conf"
		i = lepusPathInfo(confPath)
		if i["IsNotExist"] == 0 {
			w.Write(lepusMessage("Err", "Dir exist"))
			return
		}
		a, mes = lepusWriteTextFile(confPath, result, 0755)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		files, _ := ioutil.ReadDir("/var/www/public")
		for _, f := range files {
			i = lepusPathInfo("/var/www/public/" + f.Name())
			if i["IsNotExist"] == 1 || i["isDir"] == 0 || i["Readlink"] == 0 || lepusRegexp(f.Name(), "") == false {
				continue
			}
			pathReal, _ := os.Readlink("/var/www/public/" + f.Name())
			if pathReal == pathSite {
				lepusApacheAlias("add", f.Name(), confPath)
				os.RemoveAll("/var/www/public/" + f.Name())
			}
		}
	}
	lepusExecInit("/etc/init.d/apache2", "reload")
	w.Write(lepusMessage("OK", "Done"))
}

func lepusExecInit(service, val string) {
	exec.Command(service, val).Output()
}

func lepusCronAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	session, _ := store.Get(r, "lepuscp")
	user := session.Values["user"].(string)
	switch val {
	case "add":
		a, file := lepusReadTextFile("/etc/cron.d/" + user)
		if !a {
			file = ""
		}
		a, mes = lepusCheckPost(r.Form["time"], "cronTime", 10)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		a, mes = lepusCheckPost(r.Form["handler"], "", 10)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		a, mes = lepusCheckPost(r.Form["command"], "cronCommand", 255)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		time := strings.Join(r.Form["time"], "")
		handler := strings.Join(r.Form["handler"], "")
		command := strings.Join(r.Form["command"], "")
		if handler == "php" {
			handler = "/usr/bin/php"
		} else if handler == "curl" {
			handler = "/usr/bin/curl"
		} else {
			w.Write(lepusMessage("Err", "Wrong cron handler"))
			return
		}
		a, cron := lepusReadTextFile("./files/tmpl/cron.tmpl")
		if !a {
			fmt.Println(cron)
			return
		}
		cron = lepusReplaceText(cron, "%time%", time)
		cron = lepusReplaceText(cron, "%user%", user)
		cron = lepusReplaceText(cron, "%handler%", handler)
		cron = lepusReplaceText(cron, "%command%", command)
		if lepusCheckStringInText(file, cron) {
			w.Write(lepusMessage("Err", "Already exists"))
			return
		}
		a, mes = lepusWriteTextFile("/etc/cron.d/"+user, file+"\n"+cron, 0644)
		if !a {
			fmt.Println(mes)
			return
		}
		lepusExecInit("/etc/init.d/cron", "reload")
		w.Write(lepusMessage("OK", cron))

	case "del":
		a, file := lepusReadTextFile("/etc/cron.d/" + user)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		a, mes = lepusCheckPost(r.Form["task"], "no", 255)
		if !a {
			w.Write(lepusMessage("Err", mes))
			return
		}
		task := strings.Join(r.Form["task"], "")
		cron := lepusDeleteStrFromText(file, lepusBase64Decode(task))
		if len(strings.TrimSpace(cron)) == 0 {
			lepusDeleteFile("/etc/cron.d/" + user)
		} else {
			a, mes = lepusWriteTextFile("/etc/cron.d/"+user, cron, 0644)
			if !a {
				fmt.Println(mes)
				return
			}
		}
		lepusExecInit("/etc/init.d/cron", "reload")
		w.Write(lepusMessage("OK", "Done"))
	}
}

func lepusDNSAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["domain"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	domain := strings.Join(r.Form["domain"], "")
	a, named := lepusReadTextFile("/etc/bind/named.conf.local")
	if !a {
		fmt.Println(named)
		return
	}
	switch val {
	case "add":
		if lepusCheckStringInText(named, `include "/etc/bind/zone/`+domain+`";`) {
			w.Write(lepusMessage("Err", "Already exists"))
			return
		}
		a, zone := lepusReadTextFile("./files/tmpl/dns-zone.tmpl")
		if !a {
			fmt.Println(zone)
			return
		}
		a, records := lepusReadTextFile("./files/tmpl/dns-records.tmpl")
		if !a {
			fmt.Println(records)
			return
		}
		zone = lepusReplaceText(zone, "%domain%", domain)
		records = lepusReplaceText(records, "%domain%", domain)
		named += "\n" + `include "/etc/bind/zone/` + domain + `";`
		a, mes = lepusWriteTextFile("/etc/bind/named.conf.local", named, 0644)
		if !a {
			fmt.Println(mes)
			return
		}
		a, mes = lepusWriteTextFile("/etc/bind/zone/"+domain, zone, 0644)
		if !a {
			fmt.Println(mes)
			return
		}
		a, mes = lepusWriteTextFile("/etc/bind/domain/"+domain, records, 0644)
		if !a {
			fmt.Println(mes)
			return
		}
		lepusExecInit("rndc", "reload")
		w.Write(lepusMessage("OK", "Done"))

	case "del":
		named = lepusDeleteStrFromText(named, `include "/etc/bind/zone/`+domain+`";`)
		a, mes = lepusWriteTextFile("/etc/bind/named.conf.local", named, 0644)
		if !a {
			fmt.Println(mes)
			return
		}
		x, mes := lepusDeleteFile("/etc/bind/zone/" + domain)
		if !x {
			w.Write(lepusMessage("Err", mes))
			return
		}
		x, mes = lepusDeleteFile("/etc/bind/domain/" + domain)
		if !x {
			w.Write(lepusMessage("Err", mes))
			return
		}
		lepusExecInit("rndc", "reload")
		w.Write(lepusMessage("OK", "Done"))
	}
}

func lepusReplaceText(str, old, new string) string {
	x := strings.NewReplacer(old, new)
	result := x.Replace(str)
	return result
}

func lepusCheckStringInText(text, str string) bool {
	if strings.Contains(text, str) {
		return true
	}
	return false
}

func lepusDeleteStrFromText(text, str string) string {
	data := ""
	result := strings.Split(text, "\n")
	for key := range result {
		if result[key] == str {
			continue
		}
		data += result[key] + "\n"
	}
	return data
}

func lepusDeleteFile(val string) (bool, string) {
	i := lepusPathInfo(val)
	if i["isDir"] == 1 || i["Readlink"] == 1 {
		return false, "Wrong file"
	}
	if i["IsNotExist"] == 0 {
		os.RemoveAll(val)
	}
	return true, "Done"
}

func lepusCheckPost(val []string, re string, max int) (bool, string) {
	if val == nil {
		return false, "Empty post"
	}
	s := strings.Join(val, "")
	if IsBase64(s) {
		s = lepusBase64Decode(s)
	}
	if s == "" {
		return false, "Empty post val"
	}
	// domain 255, linux user 32
	if len(s) > max {
		return false, "Wrong len post"
	}
	if re != "no" {
		if lepusRegexp(s, re) == false {
			return false, "Wrong regexp post"
		}
	}
	return true, "Done"
}

func lepusCheckTextFile(path string) bool {
	i := lepusPathInfo(path)
	if i["isDir"] == 1 || i["Readlink"] == 1 {
		return false
	}
	return true
}

func lepusReadTextFile(path string) (bool, string) {
	if !lepusCheckTextFile(path) {
		return false, "Wrong file"
	}
	b, err := ioutil.ReadFile(path)
	if err == nil {
		return true, string(b)
	}
	return false, "Cant read file"
}

func lepusWriteTextFile(path, data string, perm os.FileMode) (bool, string) {
	if !lepusCheckTextFile(path) {
		return false, "Wrong file"
	}
	str := ""
	result := strings.Split(data, "\n")
	for key := range result {
		if len(result[key]) == 0 {
			continue
		}
		str += result[key] + "\n"
	}
	err := ioutil.WriteFile(path, []byte(str), perm)
	if err == nil {
		return true, "Done"
	}
	return false, "Cant write file"
}

func lepusLastModified(path string) string {
	info, _ := os.Stat(path)
	return info.ModTime().Format(http.TimeFormat)
}

func lepusRecordsDNSAPI(w http.ResponseWriter, r *http.Request) {
	if !lepusAuth(w, r) {
		w.Write(lepusMessage("Err", "Wrong auth"))
		return
	}
	r.ParseForm()
	a, mes := lepusCheckPost(r.Form["val"], "", 10)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["domain"], "", 255)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	a, mes = lepusCheckPost(r.Form["data"], "no", 1024)
	if !a {
		w.Write(lepusMessage("Err", mes))
		return
	}
	val := strings.Join(r.Form["val"], "")
	domain := strings.Join(r.Form["domain"], "")
	data := strings.Join(r.Form["data"], "")
	a, str := lepusReadTextFile("/etc/bind/domain/" + domain)
	if !a {
		w.Write(lepusMessage("Err", str))
		return
	}
	switch val {
	case "del":
		str = lepusUpdateSerial(lepusDeleteStrFromText(str, lepusBase64Decode(data)))
	case "add":
		str += "\n" + data
		str = lepusUpdateSerial(str)
	default:
		w.Write(lepusMessage("Err", "Wrong action"))
		return
	}
	a, mes = lepusWriteTextFile("/etc/bind/domain/"+domain, str, 0755)
	if !a {
		w.Write(lepusMessage("Err", str))
		return
	}
	lepusExecInit("rndc", "reload")
	w.Write(lepusMessage("OK", "Done"))
}

func lepusUpdateSerial(str string) string {
	new_serial := time.Now().Format("20060102") + "01"
	lines := strings.Split(str, "\n")
	for key := range lines {
		tabs := strings.Split(lines[key], "\t")
		if len(tabs) < 4 {
			continue
		}
		if tabs[2] == "SOA" {
			v := strings.Split(tabs[3], " ")
			serial := strings.Replace(v[2], "(", "", -1)
			if lepusGetInt(new_serial) > lepusGetInt(serial) {
				return lepusReplaceText(str, serial, new_serial)
			} else {
				return lepusReplaceText(str, serial, strconv.Itoa(lepusGetInt(serial)+1))
			}
		}
	}
	return str
}

func lepusGetInt(val string) int {
	i, _ := strconv.Atoi(val)
	return i
}

func lepusBase64Encode(val string) string {
	return base64.StdEncoding.EncodeToString([]byte(val))
}

func lepusBase64Decode(val string) string {
	result, _ := base64.StdEncoding.DecodeString(val)
	return string(result)
}

func IsBase64(val string) bool {
	mes, err := base64.StdEncoding.DecodeString(val)
	if lepusRegexp(string(mes), "base64") == false { // val = curl => true
		return false
	}
	return err == nil
}

func lepusTestAPI(w http.ResponseWriter, r *http.Request) {
	w.Write([]byte("test"))
}
