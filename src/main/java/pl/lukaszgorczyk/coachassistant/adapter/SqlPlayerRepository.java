package pl.lukaszgorczyk.coachassistant.adapter;


import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import pl.lukaszgorczyk.coachassistant.model.Player;
import pl.lukaszgorczyk.coachassistant.model.PlayerRepository;

@Repository
interface SqlPlayerRepository extends PlayerRepository, JpaRepository<Player, Integer> {
}
