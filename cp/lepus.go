package main                                                

import "fmt"
import "os"
import "bufio"
import "strings"
import "github.com/kless/osutil/user/crypt/sha512_crypt"


func CheckArgs(val []string, num int, mes string){
	if len(val) < num {
		fmt.Println(mes)
		os.Exit(0)
	}
}

func lepusCheckAuth(passwd, salt, hash string) string{
	c := sha512_crypt.New()
    x, _ := c.Generate([]byte(passwd), []byte(salt))
    return x
}

func lepusFindUser(user string) []string {
	
	var userdata []string
	file, _ := os.Open("/etc/shadow")
	defer file.Close()
	
	scanner := bufio.NewScanner(file)
    for scanner.Scan() {
		x := strings.Split(scanner.Text(), ":")		
		if x[0] == user {	
			userdata = strings.Split(x[1], "$")
			break
        }
    }
    return userdata
}

func main() {	
	var z string = "./test.go user passwd"
	CheckArgs(os.Args, 3, z)
	
	x := lepusFindUser(os.Args[1])	
	if len(x) != 4 {
		fmt.Println("No hash passwd from /etc/shadow")
		os.Exit(0)
	}
	
	new_hash := lepusCheckAuth(os.Args[2], "$"+x[1]+"$"+x[2]+"$", x[3])
    
    if "$"+x[1]+"$"+x[2]+"$"+x[3] != new_hash {
		fmt.Println("wrong password")
	}else{
		fmt.Println("right password")
	}
	fmt.Println(new_hash+"\n$"+x[1]+"$"+x[2]+"$"+x[3])
}
