#include <sys/types.h>
#include <sys/stat.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <fcntl.h>
#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <sys/ioctl.h>
#include <net/if.h>



#define ELM_NUM 4
#define STR_LEN 256
char *com_elms[ELM_NUM];
char str_buf[STR_LEN];

void sendUser(int sock_fd);
void sendPass(int sock_fd);
void Pwd(int sock_fd);
void List(int sock_fd, int modeFlag);
void Cwd(int sock_fd, char dir[]);
void Stor(int sock_fd, int modeFlag, char filename[]);
void Retr(int sock_fd, int modeFlag, char filename[]);
void Quit(int sock_fd);
void Active(int sock_fd, int* tmp_fd);
void Pasv(int sock_fd, int* data_fd);
void DisConDataSock(int data_fd);

enum Command {
  ftp,
  pwd,
  dir,
  cd,
  put,
  get,
  bye,
  COM_NUM
};
struct comname {
  int com_id;
  char* com_name;
};
struct comname com_list[COM_NUM] = {
  {ftp, "ftp"},
  {pwd, "pwd"},
  {dir, "dir"},
  {cd, "cd"},
  {put, "put"},
  {get, "get"},
  {bye, "bye"}
};
enum Command comAnalyze();

int main() {
  char buf[4096];
  enum Command com;
  int sock_fd,data_fd;
  int modeFlag = -1; // 0:PASV , 1:ACTIVE
  struct sockaddr_in sv_addr;
  ssize_t n;

  /* ftp *** の形で入力(***:address) */
  read(0, str_buf, sizeof str_buf);
  if((com = comAnalyze()) == ftp) {
    /* ソケット作成 */
    /* PASVモード */
    if(strcmp(com_elms[1], "-p") == 0) {
      printf("PASV %s",com_elms[2]);
      modeFlag = 0;
      memset(&sv_addr, 0, sizeof sv_addr);
      sv_addr.sin_family = AF_INET;
      sv_addr.sin_addr.s_addr = inet_addr(com_elms[2]);
      sv_addr.sin_port = htons(21);
      if((sock_fd = socket(PF_INET, SOCK_STREAM, 0)) < 0) {
        perror("socket");
        exit(1);
      }
      if(connect(sock_fd, (struct sockaddr *)&sv_addr, sizeof sv_addr) < 0) {
        perror("connect");
        exit(1);
      }
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
      } else if(n < 0) {
        perror("read");
        exit(1);
      }
    }
    /* Activeモード */
    else {
      modeFlag = 1;
      memset(&sv_addr, 0, sizeof sv_addr);
      sv_addr.sin_family = AF_INET;
      sv_addr.sin_addr.s_addr = inet_addr(com_elms[1]);
      sv_addr.sin_port = htons(21);
      if((sock_fd = socket(PF_INET, SOCK_STREAM, 0)) < 0) {
        perror("socket");
        exit(1);
      }
      if(connect(sock_fd, (struct sockaddr *)&sv_addr, sizeof sv_addr) < 0) {
        perror("connect");
        exit(1);
      }
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
      } else if(n < 0) {
        perror("read");
        exit(1);
      }
    }

    /* ログイン */
    sendUser(sock_fd);
    sendPass(sock_fd);
  } else {
    printf("error:ftp address\n");
    exit(1);
  }

  while(1) {
    /* コマンド入力 */
    memset(&str_buf, 0, sizeof str_buf);
    write(1, "command:", strlen("command:"));
    read(0, str_buf, sizeof str_buf);
    com = comAnalyze();
    switch(com) {
      case pwd:
      Pwd(sock_fd);
      break;
      case dir:
      List(sock_fd, modeFlag);
      break;
      case cd:
      Cwd(sock_fd, com_elms[1]);
      break;
      case put:
      Stor(sock_fd, modeFlag, com_elms[1]);
      break;
      case get:
      Retr(sock_fd, modeFlag, com_elms[1]);
      break;
      case bye:
      Quit(sock_fd);
      return 0;
      default:
      printf("retype command\n");
    }
  }
}

/* 文字列操作 */
enum Command comAnalyze() {
  com_elms[0] = strtok(str_buf, " \n");
  int i = 0;
  while(com_elms[i] != NULL) {
    i++;
    if(i == ELM_NUM) {
      printf("test1");
      return COM_NUM;
    }
    com_elms[i] = strtok(NULL, " ");
  }
  for(i=0; i<COM_NUM; i++) {
    if(strcmp(com_elms[0], com_list[i].com_name) == 0) {
      return com_list[i].com_id;
    }
  }
  return COM_NUM;
}

/* 転送ソケット切断 */
void DisConDataSock(int data_fd) {
  shutdown(data_fd, SHUT_RDWR);
}

/* PASVコマンド */
void Pasv(int sock_fd, int* data_fd) {
  char buf[4096];
  char ip_addr[16];
  int pasv_port;
  struct sockaddr_in sv_addr;
  ssize_t n;
  /* 送信 */
  memset(&buf, 0, sizeof buf);
  if(write(sock_fd, "PASV\n", strlen("PASV\n")) < 0) {
    perror("QUIT write");
    exit(1);
  }
  /* 応答 */
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    if(write(1, buf, n) < 0) {
      perror("pass write2");
      exit(1);
    }
  } else if(n < 0) {
    perror("pass read2");
    exit(1);
  }

  /* ポート, IPアドレス取得 */
  strtok(buf, "(");
  strcpy(ip_addr, strtok(NULL, ","));
  strcat(ip_addr, ".");
  strcat(ip_addr, strtok(NULL, ","));
  strcat(ip_addr, ".");
  strcat(ip_addr, strtok(NULL, ","));
  strcat(ip_addr, ".");
  strcat(ip_addr, strtok(NULL, ","));
  pasv_port = atoi(strtok(NULL, ","))*256 + atoi(strtok(NULL, ")"));

  /* 転送用ソケット作成 */
  memset(&sv_addr, 0, sizeof sv_addr);
  sv_addr.sin_family = AF_INET;
  sv_addr.sin_addr.s_addr = inet_addr(ip_addr);
  sv_addr.sin_port = htons(pasv_port);
  if((*data_fd = socket(PF_INET, SOCK_STREAM, 0)) < 0) {
    perror("socket");
    exit(1);
  }
  if(connect(*data_fd, (struct sockaddr *)&sv_addr, sizeof sv_addr) < 0) {
    perror("connect");
    exit(1);
  }
}

/* ユーザコマンド */
void sendUser(int sock_fd) {
  char buf[2048];
  char user[100] = "USER ";
  ssize_t n;
  /* 送信 */
  memset(&buf, 0, sizeof buf);
  if(write(1, "Name:", 5) > 0) {
    if((n = read(0, buf, sizeof buf)) < 0) {
      perror("user read");
      exit(1);
    } else {
      strcat(user, buf);
      if(write(sock_fd, user, strlen(user)) < 0) {
        perror("user write");
        exit(1);
      }
    }
  } else exit(1);
  /* 応答 */
  memset(&buf, 0, sizeof buf);
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    if(write(1, buf, n) < 0) {
      perror("user write2");
      exit(1);
    }
  } else if(n < 0)  {
    perror("user read2");
    exit(1);
  }
}


/* パスワードコマンド */
void sendPass(int sock_fd) {
  char buf[2048];
  char pass[100] = "PASS ";
  ssize_t n;
  /* 送信 */
  memset(&buf, 0, sizeof buf);
  if(write(1, "password:", 10) > 0) {
    if((n = read(0, buf, sizeof buf)) < 0) {
      perror("pass read");
      exit(1);
    } else {
      strcat(pass, buf);
      if(write(sock_fd, pass, strlen(pass)) < 0) {
        perror("pass write");
        exit(1);
      }
    }
  }
  /* 応答 */
  memset(&buf, 0, sizeof buf);
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    if(write(1, buf, n) < 0) {
      perror("pass write2");
      exit(1);
    }
  } else if(n < 0) {
    perror("pass read2");
    exit(1);
  }
}

/* PWDコマンド */
void Pwd(int sock_fd) {
  char buf[4096];
  ssize_t n;
  /* 送信 */
  memset(&buf, 0, sizeof buf);
  if(write(sock_fd, "PWD\n", strlen("PWD\n")) > 0) {
    if((n = read(sock_fd, buf, sizeof buf)) > 0) {
      write(1, buf, sizeof buf);
    }
  }
}

/* LISTコマンド */
void List(int sock_fd, int modeFlag) {
  int data_fd;
  int tmp_fd;
  struct sockaddr_in cl_addr;
  socklen_t cl_len;
  ssize_t n;
  char buf[4096];
  /* PASVコマンド */
  memset(&buf, 0, sizeof buf);
  if(modeFlag == 0) Pasv(sock_fd, &data_fd);
  else if(modeFlag == 1) Active(sock_fd, &tmp_fd);
  else exit(1);
  /* LIST送信・応答 */
  if(modeFlag == 0) {
    /* 送信 */
    if(write(sock_fd, "LIST\n", strlen("LIST\n")) > 0) {
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
      }
    }
    /* 応答 */
    memset(&buf, 0, sizeof buf);
    if((n = read(data_fd, buf, sizeof buf)) > 0) {
      write(1, buf, n);
    }
  }
  else if(modeFlag == 1) {
    if(write(sock_fd, "LIST\n", strlen("LIST\n")) > 0) {
      cl_len = sizeof cl_addr;
      if((data_fd = accept(tmp_fd, (struct sockaddr *)&cl_addr, &cl_len)) < 0) {
        perror("accept");
        exit(1);
      }
      /* LIST応答 */
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
        memset(&buf, 0, sizeof buf);
      }
      else exit(1);
      /* LIST データ */
      if((n = read(data_fd, buf, sizeof buf)) > 0) write(1, buf, sizeof buf);
    }
  }
  /* コネクション切断 */
  DisConDataSock(data_fd);
  if(modeFlag == 1) DisConDataSock(tmp_fd);
  /* List 終了応答 */
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    write(1, buf, n);
  } else exit(1);
}

/* Active 接続 */
void Active(int sock_fd, int *tmp_fd) {
  char buf[4096];
  char port[1024];
  char ip_addr[16] = "";
  int port_up, port_low;
  int data_port;
  int optval = 1;
  struct sockaddr_in sv_addr;
  struct ifreq ifr;
  ssize_t n;

  memset(&buf, 0, sizeof buf);
  memset(&sv_addr, 0, sizeof sv_addr);
  socklen_t sv_len = sizeof sv_addr;
  sv_addr.sin_family = AF_INET;
  sv_addr.sin_addr.s_addr = htonl(INADDR_ANY);
  sv_addr.sin_port = htons(0);
  if((*tmp_fd = socket(PF_INET, SOCK_STREAM, 0)) < 0) {
    perror("socket");
    exit(1);
  }
  if(setsockopt(*tmp_fd, SOL_SOCKET, SO_REUSEADDR, (const char *)&optval, sizeof optval) < 0) {
    perror("setsockopt");
    exit(1);
  }
  if(bind(*tmp_fd, (struct sockaddr *)&sv_addr, sizeof sv_addr) < 0) {
    perror("bind");
    exit(1);
  }
  if(listen(*tmp_fd, SOMAXCONN) < 0) {
    perror("listen");
    exit(1);
  }
  if((getsockname(*tmp_fd, (struct sockaddr *)&sv_addr, &sv_len)) < 0) {
    perror("getsockname");
    exit(1);
  }
  strncpy(ifr.ifr_name, "en0", IFNAMSIZ-1);
  ioctl(sock_fd, SIOCGIFADDR, &ifr);
  for(int i=0; i<13; i++) {
    int a = 0xff &ifr.ifr_addr.sa_data[i];
    int b = 0xff &ifr.ifr_addr.sa_data[i+1];
    char c[10];
    if(a != 0) {
      sprintf(c,"%d",a);
      strcat(ip_addr, c);
      if(b != 0) {
        strcat(ip_addr, ",");
      }
    }
  }
  for(int i=0; i<strlen(ip_addr); i++) {
    if(ip_addr[i] == '.') {
      ip_addr[i] = ',';
    }
  }
  port_up = ((int)htons(sv_addr.sin_port) / 256);
  port_low = ((int)htons(sv_addr.sin_port) - (port_up*256));
  sprintf(port, "PORT %s,%d,%d\r\n", ip_addr, port_up, port_low);
  write(sock_fd, port, strlen(port));
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    write(1, buf, n);
  }
}

/* CWDコマンド */
void Cwd(int sock_fd, char dir[]) {
  char buf[4096];
  char command[1024];
  ssize_t n;
  /* 送信 */
  sprintf(command,"CWD %s\n",dir);
  memset(&buf, 0, sizeof buf);
  if(write(sock_fd, command, strlen(command)) > 0) {
    if((n = read(sock_fd, buf, sizeof buf)) > 0) {
      write(1, buf, sizeof buf);
    }
  }
}

/* STORE(put)コマンド */
void Stor(int sock_fd, int modeFlag, char filename[]) {
  int data_fd;
  int tmp_fd;
  int fd_w;
  struct sockaddr_in cl_addr;
  char buf[4096],file_buf[4096];
  char command[1024];
  socklen_t cl_len;
  ssize_t n,file_n;
  /* PASVコマンド */
  memset(&buf, 0, sizeof buf);
  memset(&file_buf, 0, sizeof file_buf);
  if(modeFlag == 0) Pasv(sock_fd, &data_fd);
  else if(modeFlag == 1) Active(sock_fd, &tmp_fd);
  else exit(1);
  /* STOR送信・応答 */
  sprintf(command,"STOR %s",filename);
  if((fd_w = open(strtok(filename,"\r\n"), O_RDONLY)) > 0) {
    if((file_n = read(fd_w, file_buf, sizeof file_buf)) < 0) {
      perror("read");
      exit(1);
    }
  } else {
    perror("stor open");
    exit(1);
  }
  if(modeFlag == 0) {
    /* STOR送信 */
    if(write(sock_fd, command, strlen(command)) > 0) {
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
      }
    }
    /* データ送信 */
    if(write(data_fd, file_buf, file_n) < 0) exit(1);
  }
  else if(modeFlag == 1) {
    /* STOR送信 */
    if(write(sock_fd, command, strlen(command)) > 0) {
      cl_len = sizeof cl_addr;
      if((data_fd = accept(tmp_fd, (struct sockaddr *)&cl_addr, &cl_len)) < 0) {
        perror("accept");
        exit(1);
      }
      /* STOR応答 */
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
        memset(&buf, 0, sizeof buf);
      }
      else {
        perror("stor read");
        exit(1);
      }
      /* データ送信 */
      if(write(data_fd, file_buf, file_n) < 0) {
        perror(" stor write");
        exit(1);
      }
    } else {
      perror("stor write");
    }
  }
  /* コネクション切断 */
  DisConDataSock(data_fd);
  if(modeFlag == 1) DisConDataSock(tmp_fd);
  /* STOR 終了応答 */
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    write(1, buf, n);
  } else {
    perror("stor end read ");
    exit(1);
  }
  close(fd_w);
}

/* RETR(get)コマンド */
void Retr(int sock_fd, int modeFlag, char filename[]) {
  int data_fd;
  int tmp_fd;
  int fd_w;
  struct sockaddr_in cl_addr;
  char buf[4096],file_buf[4096];
  char command[1024];
  socklen_t cl_len;
  ssize_t n,file_n;
  /* PASVコマンド */
  memset(&buf, 0, sizeof buf);
  memset(&file_buf, 0, sizeof file_buf);
  if(modeFlag == 0) Pasv(sock_fd, &data_fd);
  else if(modeFlag == 1) Active(sock_fd, &tmp_fd);
  else exit(1);
  /* RETR送信・応答 */
  sprintf(command,"RETR %s",filename);
  if((fd_w = open(strtok(filename,"\r\n"), O_WRONLY|O_CREAT|O_TRUNC, 0666)) < 0) {
    perror("retr open");
    exit(1);
  }
  if(modeFlag == 0) {
    /* RETR送信 */
    if(write(sock_fd, command, strlen(command)) > 0) {
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
      }
    }
    /* データ受信 */
    if((n = read(data_fd, file_buf, sizeof file_buf)) > 0) {
      write(fd_w, file_buf, n);
    } else {
      perror("retr read data_fd");
      exit(1);
    }
  }
  else if(modeFlag == 1) {
    /* RETR送信 */
    if(write(sock_fd, command, strlen(command)) > 0) {
      cl_len = sizeof cl_addr;
      if((data_fd = accept(tmp_fd, (struct sockaddr *)&cl_addr, &cl_len)) < 0) {
        perror("accept");
        exit(1);
      }
      /* RETR応答 */
      if((n = read(sock_fd, buf, sizeof buf)) > 0) {
        write(1, buf, n);
        memset(&buf, 0, sizeof buf);
      }
      else exit(1);
      /* データ受信 */
      if((n = read(data_fd, file_buf, sizeof file_buf)) > 0) {
        write(fd_w, file_buf, n);
      } else {
        perror("retr read data_fd");
        exit(1);
      }
    }
  }
  /* コネクション切断 */
  DisConDataSock(data_fd);
  if(modeFlag == 1) DisConDataSock(tmp_fd);
  /* STOR 終了応答 */
  if((n = read(sock_fd, buf, sizeof buf)) > 0) {
    write(1, buf, n);
  } else exit(1);
  close(fd_w);
}

void Quit(int sock_fd) {
  char buf[4096];
  ssize_t n;
  /* QUIT 送信 */
  memset(&buf, 0, sizeof buf);
  if(write(sock_fd, "QUIT\r\n", strlen("QUIT\r\n")) > 0) {
    if((n = read(sock_fd, buf, sizeof buf)) > 0) {
      if(write(1, buf, n) < 0) {
        perror("quit write");
        exit(1);
      }
    } else {
      perror("quit read");
      exit(1);
    }
  } else {
    perror("QUIT write");
    exit(1);
  }
  /* 応答 */
  while((n = read(sock_fd, buf, sizeof buf)) > 0) {
    if(write(1, buf, n) < 0) {
      perror("pass write2");
      exit(1);
    }
  }
  if(n < 0) {
    perror("pass read2");
    exit(1);
  }
  /* ソケット削除 */
  if(shutdown(sock_fd, SHUT_RDWR) < 0) {
    perror("shutdown");
    exit(1);
  }
}
